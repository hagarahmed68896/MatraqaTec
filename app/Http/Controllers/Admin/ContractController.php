<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\MaintenanceCompany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

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

        $items = $query->paginate(15);
        return view('admin.contracts.index', compact('items'));
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
            'project_value'          => 'required|numeric',
            'start_date'             => 'required|date',
            'end_date'               => 'required|date|after:start_date',
            'status'                 => 'required|in:active,expired,completed',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        DB::transaction(function () use ($request) {
            Contract::create($request->all());
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
        $item = Contract::findOrFail($id);
        $companies = MaintenanceCompany::all();
        return view('admin.contracts.edit', compact('item', 'companies'));
    }

    public function update(Request $request, $id)
    {
        $contract = Contract::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'contract_number'        => 'required|string|unique:contracts,contract_number,' . $id,
            'maintenance_company_id' => 'required|exists:maintenance_companies,id',
            'project_value'          => 'required|numeric',
            'start_date'             => 'required|date',
            'end_date'               => 'required|date|after:start_date',
            'status'                 => 'required|in:active,expired,completed',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        DB::transaction(function () use ($request, $contract) {
            $contract->update($request->all());
        });

        return redirect()->route('admin.contracts.index')->with('success', __('Contract updated successfully.'));
    }

    public function destroy($id)
    {
        $contract = Contract::findOrFail($id);
        $contract->delete();
        return redirect()->route('admin.contracts.index')->with('success', __('Contract deleted successfully.'));
    }

    public function download()
    {
        $contracts = Contract::with('maintenanceCompany')->get();
        return $this->generateCsv($contracts, "contracts.csv");
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
                $contract->end_date ? $contract->end_date->format('Y-m-d') : '',
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
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]
        );
    }
}
