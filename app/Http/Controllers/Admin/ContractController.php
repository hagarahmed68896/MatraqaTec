<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\MaintenanceCompany;
use App\Models\CorporateCustomer;
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
                  })
                  ->orWhereHas('corporateCustomer', function ($q2) use ($search) {
                      $q2->where('company_name_en', 'like', "%{$search}%")
                         ->orWhere('company_name_ar', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by tab
        if ($request->filled('tab')) {
            switch ($request->tab) {
                case 'requests':
                    $query->whereIn('status', ['pending', 'under_review', 'offer_sent', 'offer_rejected']);
                    break;
                case 'awaiting':
                    $query->whereIn('status', ['offer_accepted', 'awaiting_signed_contract', 'awaiting_payment_proof']);
                    break;
                case 'active':
                    $query->where('status', 'active');
                    break;
                case 'archived':
                    $query->whereIn('status', ['completed', 'expired']);
                    break;
            }
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

        $totalContracts = Contract::count();
        $totalContractsLastWeek = Contract::where('created_at', '<', \Carbon\Carbon::now()->subWeek())->count();
        $totalContractsGrowth = $totalContractsLastWeek > 0 ? (($totalContracts - $totalContractsLastWeek) / $totalContractsLastWeek) * 100 : ($totalContracts > 0 ? 100 : 0);

        $totalCompanies = Contract::distinct('maintenance_company_id')->count();
        $totalCompaniesLastWeek = Contract::where('created_at', '<', \Carbon\Carbon::now()->subWeek())->distinct('maintenance_company_id')->count();
        $totalCompaniesGrowth = $totalCompaniesLastWeek > 0 ? (($totalCompanies - $totalCompaniesLastWeek) / $totalCompaniesLastWeek) * 100 : ($totalCompanies > 0 ? 100 : 0);

        $collectedAmount = Contract::sum('paid_amount');
        $collectedAmountLastWeek = Contract::where('created_at', '<', \Carbon\Carbon::now()->subWeek())->sum('paid_amount');
        $collectedAmountGrowth = $collectedAmountLastWeek > 0 ? (($collectedAmount - $collectedAmountLastWeek) / $collectedAmountLastWeek) * 100 : ($collectedAmount > 0 ? 100 : 0);

        $expiredContracts = Contract::where('status', 'expired')->orWhereDate('end_date', '<', now())->count();
        $expiredContractsLastWeek = Contract::where('created_at', '<', \Carbon\Carbon::now()->subWeek())->where(function($q) { $q->where('status', 'expired')->orWhereDate('end_date', '<', now()); })->count();
        $expiredContractsGrowth = $expiredContractsLastWeek > 0 ? (($expiredContracts - $expiredContractsLastWeek) / $expiredContractsLastWeek) * 100 : ($expiredContracts > 0 ? 100 : 0);

        $stats = [
            'total_contracts'   => $totalContracts,
            'total_contracts_trend' => ($totalContractsGrowth >= 0 ? '+' : '') . number_format($totalContractsGrowth, 2) . '%',
            'total_contracts_trend_value' => $totalContractsGrowth,
            'total_companies'   => $totalCompanies,
            'total_companies_trend' => ($totalCompaniesGrowth >= 0 ? '+' : '') . number_format($totalCompaniesGrowth, 2) . '%',
            'total_companies_trend_value' => $totalCompaniesGrowth,
            'collected_amount'  => $collectedAmount,
            'collected_amount_trend' => ($collectedAmountGrowth >= 0 ? '+' : '') . number_format($collectedAmountGrowth, 2) . '%',
            'collected_amount_trend_value' => $collectedAmountGrowth,
            'expired_contracts' => $expiredContracts,
            'expired_contracts_trend' => ($expiredContractsGrowth >= 0 ? '+' : '') . number_format($expiredContractsGrowth, 2) . '%',
            'expired_contracts_trend_value' => $expiredContractsGrowth,
        ];

        $items = $query->paginate(15);
        return view('admin.contracts.index', compact('items', 'stats'));
    }

    public function create()
    {
        $companies = MaintenanceCompany::all();
        $corporate_customers = CorporateCustomer::all();
        return view('admin.contracts.create', compact('companies', 'corporate_customers'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'contract_number'          => 'nullable|string|unique:contracts',
            'maintenance_company_id'   => 'nullable|exists:maintenance_companies,id',
            'corporate_customer_id'    => 'nullable|exists:corporate_customers,id',
            'project_value'            => 'nullable|numeric|min:0',
            'start_date'               => 'nullable|date',
            'end_date'                 => 'nullable|date|after:start_date',
            'status'                   => 'required|in:pending,under_review,offer_sent,offer_accepted,offer_rejected,awaiting_signed_contract,awaiting_payment_proof,active,completed,expired',
            'contract_file'            => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
            'price_offer_file'         => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
            'contact_numbers'          => 'nullable|array',
            'contact_numbers.*'        => 'nullable|string|max:30',
            // Request fields
            'company_name'             => 'nullable|string|max:255',
            'commercial_record_number' => 'nullable|string|max:255',
            'head_office'              => 'nullable|string|max:255',
            'contact_number'           => 'nullable|string|max:255',
            'service_description'      => 'nullable|string',
            'boq_file'                 => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx,zip,rar|max:10240',
            'drawings_file'            => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx,zip,rar|max:10240',
            'other_docs_file'          => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx,zip,rar|max:10240',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $data = $request->except([
            'contract_file', 'price_offer_file', 'contact_numbers',
            'boq_file', 'drawings_file', 'other_docs_file'
        ]);

        // Auto-generate contract number if not provided
        if (empty($data['contract_number'])) {
            $data['contract_number'] = 'CNT-' . strtoupper(uniqid());
        }

        // Handle file uploads
        if ($request->hasFile('contract_file')) {
            $file = $request->file('contract_file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/contracts'), $filename);
            $data['contract_file'] = 'uploads/contracts/' . $filename;
        }

        if ($request->hasFile('price_offer_file')) {
            $file = $request->file('price_offer_file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/contracts/price_offers'), $filename);
            $data['price_offer_file'] = 'uploads/contracts/price_offers/' . $filename;
        }

        if ($request->hasFile('boq_file')) {
            $file = $request->file('boq_file');
            $filename = time() . '_boq_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/contracts/attachments'), $filename);
            $data['boq_file'] = 'uploads/contracts/attachments/' . $filename;
        }

        if ($request->hasFile('drawings_file')) {
            $file = $request->file('drawings_file');
            $filename = time() . '_drawings_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/contracts/attachments'), $filename);
            $data['drawings_file'] = 'uploads/contracts/attachments/' . $filename;
        }

        if ($request->hasFile('other_docs_file')) {
            $file = $request->file('other_docs_file');
            $filename = time() . '_other_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/contracts/attachments'), $filename);
            $data['other_docs_file'] = 'uploads/contracts/attachments/' . $filename;
        }

        // Serialize contact numbers
        if ($request->filled('contact_numbers')) {
            $phones = array_filter($request->contact_numbers);
            $data['contact_numbers'] = implode(',', $phones);
        }

        $contract = null;
        DB::transaction(function () use ($data, &$contract) {
            $contract = Contract::create($data);
        });

        return redirect()->route('admin.contracts.index')->with('success', __('Contract created successfully.'));
    }

    public function show($id)
    {
        $item = Contract::with(['maintenanceCompany', 'corporateCustomer', 'paymentReceipts', 'user'])->findOrFail($id);
        return view('admin.contracts.show', compact('item'));
    }

    public function edit($id)
    {
        $item     = Contract::findOrFail($id);
        $companies = MaintenanceCompany::all();
        $corporate_customers = CorporateCustomer::all();
        return view('admin.contracts.edit', compact('item', 'companies', 'corporate_customers'));
    }

    public function update(Request $request, $id)
    {
        $contract = Contract::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'contract_number'          => 'nullable|string|unique:contracts,contract_number,' . $id,
            'maintenance_company_id'   => 'nullable|exists:maintenance_companies,id',
            'corporate_customer_id'    => 'nullable|exists:corporate_customers,id',
            'project_value'            => 'nullable|numeric|min:0',
            'start_date'               => 'nullable|date',
            'end_date'                 => 'nullable|date|after:start_date',
            'status'                   => 'required|in:pending,under_review,offer_sent,offer_accepted,offer_rejected,awaiting_signed_contract,awaiting_payment_proof,active,completed,expired',
            'contract_file'            => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
            'price_offer_file'         => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
            'contact_numbers'          => 'nullable|array',
            'contact_numbers.*'        => 'nullable|string|max:30',
            // Request fields
            'company_name'             => 'nullable|string|max:255',
            'commercial_record_number' => 'nullable|string|max:255',
            'head_office'              => 'nullable|string|max:255',
            'contact_number'           => 'nullable|string|max:255',
            'service_description'      => 'nullable|string',
            'rejection_reason'         => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $oldStatus = $contract->status;

        $data = $request->except([
            'contract_file', 'price_offer_file', 'contact_numbers', 
            'delete_contract_file', 'delete_price_offer_file'
        ]);

        // Keep existing contract number if not submitted
        if (empty($data['contract_number'])) {
            $data['contract_number'] = $contract->contract_number;
        }

        // Handle file deletion requests
        if ($request->input('delete_contract_file') == '1' && $contract->contract_file) {
            $oldPath = public_path($contract->contract_file);
            if (file_exists($oldPath)) {
                unlink($oldPath);
            }
            $data['contract_file'] = null;
        }

        if ($request->input('delete_price_offer_file') == '1' && $contract->price_offer_file) {
            $oldPath = public_path($contract->price_offer_file);
            if (file_exists($oldPath)) {
                unlink($oldPath);
            }
            $data['price_offer_file'] = null;
        }

        // Handle new file uploads
        if ($request->hasFile('contract_file')) {
            if ($contract->contract_file) {
                $oldPath = public_path($contract->contract_file);
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }
            $file = $request->file('contract_file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/contracts'), $filename);
            $data['contract_file'] = 'uploads/contracts/' . $filename;
            
            // If admin uploads a contract template, status becomes awaiting_signed_contract
            if ($data['status'] === 'offer_accepted') {
                $data['status'] = 'awaiting_signed_contract';
            }
        }

        if ($request->hasFile('price_offer_file')) {
            if ($contract->price_offer_file) {
                $oldPath = public_path($contract->price_offer_file);
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }
            $file = $request->file('price_offer_file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/contracts/price_offers'), $filename);
            $data['price_offer_file'] = 'uploads/contracts/price_offers/' . $filename;
        }

        // Serialize contact numbers
        if ($request->has('contact_numbers')) {
            $phones = array_filter($request->contact_numbers ?? []);
            $data['contact_numbers'] = $phones ? implode(',', $phones) : null;
        }

        DB::transaction(function () use ($data, $contract) {
            $contract->update($data);
        });

        // Trigger Notifications on Status Changes
        $newStatus = $contract->status;
        if ($oldStatus !== $newStatus && $contract->user) {
            try {
                if ($newStatus === 'offer_sent') {
                    $contract->user->sendFcmNotification([
                        'type' => 'contract_offer_sent',
                        'title_ar' => 'عرض سعر جديد',
                        'title_en' => 'New Price Offer',
                        'body_ar' => 'تم إرسال عرض السعر لطلب التعاقد الخاص بكم. يرجى المراجعة والرد.',
                        'body_en' => 'A price offer has been sent for your contract request. Please review and respond.',
                        'data' => ['contract_id' => $contract->id]
                    ], true);
                } elseif ($newStatus === 'awaiting_signed_contract') {
                    $contract->user->sendFcmNotification([
                        'type' => 'contract_ready_for_signature',
                        'title_ar' => 'عقد جاهز للتوقيع',
                        'title_en' => 'Contract Ready for Signature',
                        'body_ar' => 'عقدكم جاهز للتوقيع. يرجى تحميله والتوقيع عليه وإعادة رفعه.',
                        'body_en' => 'Your contract is ready. Please download, sign, and upload the signed copy.',
                        'data' => ['contract_id' => $contract->id]
                    ], true);
                } elseif ($newStatus === 'active') {
                    $contract->user->sendFcmNotification([
                        'type' => 'contract_activated',
                        'title_ar' => 'تم تفعيل التعاقد',
                        'title_en' => 'Contract Activated',
                        'body_ar' => 'تهانينا! تم تفعيل عقدكم بنجاح ومتابعة حالته.',
                        'body_en' => 'Congratulations! Your contract has been successfully activated.',
                        'data' => ['contract_id' => $contract->id]
                    ], true);
                }
            } catch (\Exception $e) {
                \Log::error('FCM Notification error in Admin ContractController: ' . $e->getMessage());
            }
        }

        return redirect()->route('admin.contracts.index')->with('success', __('Contract updated successfully.'));
    }

    public function destroy($id)
    {
        $contract = Contract::findOrFail($id);

        $files = [
            $contract->contract_file,
            $contract->price_offer_file,
            $contract->boq_file,
            $contract->drawings_file,
            $contract->other_docs_file,
            $contract->signed_contract_file
        ];

        foreach ($files as $file) {
            if ($file) {
                $oldPath = public_path($file);
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }
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
        fputcsv($handle, ['ID', 'Contract Number', 'Maintenance Company', 'Corporate Customer', 'Value', 'Status', 'Start Date', 'End Date']);

        foreach ($contracts as $contract) {
            fputcsv($handle, [
                $contract->id,
                $contract->contract_number,
                $contract->maintenanceCompany ? ($contract->maintenanceCompany->company_name_en ?? $contract->maintenanceCompany->name) : 'N/A',
                $contract->corporateCustomer ? ($contract->corporateCustomer->company_name_en ?? $contract->corporateCustomer->company_name_ar) : 'N/A',
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
