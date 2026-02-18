<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\MaintenanceCompany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ContractController extends Controller
{
    public function index(Request $request)
    {
        $query = Contract::with('maintenanceCompany');

        // Search by contract number or company name
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('contract_number', 'like', "%{$search}%")
                  ->orWhereHas('maintenanceCompany', function ($q2) use ($search) {
                      $q2->where('company_name_en', 'like', "%{$search}%")
                         ->orWhere('company_name_ar', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by maintenance company
        if ($request->has('maintenance_company_id')) {
            $query->where('maintenance_company_id', $request->maintenance_company_id);
        }

        // Sorting
        if ($request->has('sort_by')) {
            switch ($request->sort_by) {
                case 'name':
                    $query->join('maintenance_companies', 'contracts.maintenance_company_id', '=', 'maintenance_companies.id')
                          ->orderBy('maintenance_companies.company_name_en', 'asc')
                          ->select('contracts.*');
                    break;
                case 'oldest':
                    $query->orderBy('created_at', 'asc');
                    break;
                case 'newest':
                default:
                    $query->orderBy('created_at', 'desc');
                    break;
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $stats = [
            'total_contracts'   => Contract::count(),
            'total_companies'   => Contract::distinct('maintenance_company_id')->count(),
            'collected_amount'  => Contract::sum('paid_amount'),
            'expired_contracts' => Contract::where('status', 'expired')->orWhereDate('end_date', '<', now())->count(),
        ];

        $items = $query->paginate(15);
        return view('admin.contracts.index', compact('items', 'stats'));
    }

    public function create()
    {
        $companies = MaintenanceCompany::all();
        return view('admin.contracts.create', compact('companies'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'contract_number'        => 'required|string|unique:contracts',
            'maintenance_company_id' => 'required|exists:maintenance_companies,id',
            'project_value'          => 'required|numeric|min:0',
            'start_date'             => 'required|date',
            'end_date'               => 'required|date|after:start_date',
            'status'                 => 'required|in:active,expired,completed',
            'contract_file'          => 'nullable|file|mimes:pdf|max:5120',
            'contact_numbers'        => 'nullable|array',
            'contact_numbers.*'      => 'nullable|string|max:30',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $data = $request->except(['contract_file', 'contact_numbers']);

        // Handle file upload
        if ($request->hasFile('contract_file')) {
            $data['contract_file'] = $request->file('contract_file')->store('contracts', 'public');
        }

        // Serialize contact numbers
        if ($request->filled('contact_numbers')) {
            $phones = array_filter($request->contact_numbers);
            $data['contact_numbers'] = implode(',', $phones);
        }

        DB::transaction(function () use ($data) {
            Contract::create($data);
        });

        return redirect()->route('admin.contracts.index')->with('success', __('Contract created successfully.'));
    }

    public function show($id)
    {
        $item = Contract::with('maintenanceCompany')->findOrFail($id);
        return view('admin.contracts.show', compact('item'));
    }

    public function edit($id)
    {
        $item     = Contract::findOrFail($id);
        $companies = MaintenanceCompany::all();
        return view('admin.contracts.edit', compact('item', 'companies'));
    }

    public function update(Request $request, $id)
    {
        $contract = Contract::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'contract_number'        => 'required|string|unique:contracts,contract_number,' . $id,
            'maintenance_company_id' => 'required|exists:maintenance_companies,id',
            'project_value'          => 'required|numeric|min:0',
            'start_date'             => 'required|date',
            'end_date'               => 'required|date|after:start_date',
            'status'                 => 'required|in:active,expired,completed',
            'contract_file'          => 'nullable|file|mimes:pdf|max:5120',
            'contact_numbers'        => 'nullable|array',
            'contact_numbers.*'      => 'nullable|string|max:30',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $data = $request->except(['contract_file', 'contact_numbers', 'delete_contract_file']);

        // Handle file deletion request
        if ($request->input('delete_contract_file') == '1' && $contract->contract_file) {
            Storage::disk('public')->delete($contract->contract_file);
            $data['contract_file'] = null;
        }

        // Handle new file upload (replaces old one)
        if ($request->hasFile('contract_file')) {
            if ($contract->contract_file) {
                Storage::disk('public')->delete($contract->contract_file);
            }
            $data['contract_file'] = $request->file('contract_file')->store('contracts', 'public');
        }

        // Serialize contact numbers
        if ($request->has('contact_numbers')) {
            $phones = array_filter($request->contact_numbers ?? []);
            $data['contact_numbers'] = $phones ? implode(',', $phones) : null;
        }

        DB::transaction(function () use ($data, $contract) {
            $contract->update($data);
        });

        return redirect()->route('admin.contracts.index')->with('success', __('Contract updated successfully.'));
    }

    public function destroy($id)
    {
        $contract = Contract::findOrFail($id);

        if ($contract->contract_file) {
            Storage::disk('public')->delete($contract->contract_file);
        }

        $contract->delete();
        return redirect()->route('admin.contracts.index')->with('success', __('Contract deleted successfully.'));
    }

    public function download()
    {
        $contracts = Contract::with('maintenanceCompany')->get();
        return $this->generateCsv($contracts, 'contracts.csv');
    }

    private function generateCsv($contracts, $filename)
    {
        $handle = fopen('php://memory', 'w');
        fputcsv($handle, ['ID', 'Contract Number', 'Company Name', 'Value', 'Status', 'Start Date', 'End Date']);

        foreach ($contracts as $contract) {
            fputcsv($handle, [
                $contract->id,
                $contract->contract_number,
                $contract->maintenanceCompany ? $contract->maintenanceCompany->company_name_en : 'N/A',
                $contract->project_value,
                $contract->status,
                $contract->start_date ? $contract->start_date->format('Y-m-d') : '',
                $contract->end_date   ? $contract->end_date->format('Y-m-d')   : '',
            ]);
        }

        fseek($handle, 0);

        return response()->stream(
            function () use ($handle) {
                fpassthru($handle);
                fclose($handle);
            },
            200,
            [
                'Content-Type'        => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]
        );
    }
}
