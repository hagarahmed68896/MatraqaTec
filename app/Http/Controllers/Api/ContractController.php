<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use Illuminate\Http\Request;

class ContractController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        if (!$user) return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
        
        $query = Contract::with(['maintenanceCompany', 'paymentReceipts']);

        if ($user->type === 'maintenance_company') {
            $company = $user->maintenanceCompany;
            $query->where('maintenance_company_id', $company->id ?? 0);
        } elseif ($user->type === 'corporate_customer') {
            $corporate = $user->corporateCustomer;
            $query->where('corporate_customer_id', $corporate->id ?? 0)
                  ->orWhere('user_id', $user->id);
        } elseif ($user->type === 'technician') {
            $technician = $user->technician;
            $query->where('maintenance_company_id', $technician->maintenance_company_id ?? 0);
        } else {
            // General users or individuals might not have contracts
            $query->where('user_id', $user->id);
        }

        $contracts = $query->latest()->get();
            
        return response()->json(['status' => true, 'message' => 'Contracts retrieved', 'data' => $contracts]);
    }

    public function show($id)
    {
        $user = auth()->user();
        if (!$user) return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);

        $query = Contract::with(['maintenanceCompany', 'paymentReceipts']);

        if ($user->type === 'maintenance_company') {
            $company = $user->maintenanceCompany;
            $query->where('maintenance_company_id', $company->id ?? 0);
        } elseif ($user->type === 'corporate_customer') {
            $corporate = $user->corporateCustomer;
            $query->where(function($q) use ($corporate, $user) {
                $q->where('corporate_customer_id', $corporate->id ?? 0)
                  ->orWhere('user_id', $user->id);
            });
        } elseif ($user->type === 'technician') {
            $technician = $user->technician;
            $query->where('maintenance_company_id', $technician->maintenance_company_id ?? 0);
        } else {
            $query->where('user_id', $user->id);
        }

        $contract = $query->find($id);
            
        if (!$contract) return response()->json(['status' => false, 'message' => 'Contract not found'], 404);
        return response()->json(['status' => true, 'message' => 'Contract retrieved', 'data' => $contract]);
    }

    /**
     * Store a new Contract Tendering Request
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        if (!$user) return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);

        $request->validate([
            'company_name'             => 'required|string|max:255',
            'commercial_record_number' => 'required|string|max:255',
            'head_office'              => 'required|string|max:255',
            'contact_number'           => 'required|string|max:255',
            'service_description'      => 'required|string',
            'boq_file'                 => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx,zip,rar|max:10240',
            'drawings_file'            => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx,zip,rar|max:10240',
            'other_docs_file'          => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx,zip,rar|max:10240',
        ]);

        // Check if the corporate customer already has a pending or under review request
        $hasPending = Contract::where('corporate_customer_id', $user->corporateCustomer?->id)
            ->whereIn('status', ['pending', 'under_review'])
            ->exists();

        if ($hasPending) {
            return response()->json([
                'status' => false,
                'message' => 'لديك طلب تعاقد قيد المراجعة بالفعل. يرجى انتظار رد الإدارة قبل تقديم طلب جديد.'
            ], 422);
        }

        $data = $request->only([
            'company_name',
            'commercial_record_number',
            'head_office',
            'contact_number',
            'service_description'
        ]);

        // Upload attachments
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

        $data['contract_number'] = 'CNT-REQ-' . strtoupper(uniqid());
        $data['status'] = 'pending';
        $data['user_id'] = $user->id;
        $data['corporate_customer_id'] = $user->corporateCustomer?->id;
        $data['project_value'] = null;
        $data['paid_amount'] = 0;
        $data['remaining_amount'] = 0;

        $contract = Contract::create($data);

        // Notify Admins
        try {
            $admins = \App\Models\User::where('type', 'admin')->get();
            foreach ($admins as $admin) {
                $admin->sendFcmNotification([
                    'type' => 'new_contract_request',
                    'title_ar' => 'طلب تعاقد جديد',
                    'title_en' => 'New Contract Request',
                    'body_ar' => 'تم استلام طلب تعاقد جديد من شركة ' . $contract->company_name,
                    'body_en' => 'A new contract request has been received from company ' . $contract->company_name,
                    'data' => ['contract_id' => $contract->id]
                ], true);
            }
        } catch (\Exception $e) {
            \Log::error('FCM Error (new_contract_request): ' . $e->getMessage());
        }

        return response()->json([
            'status' => true,
            'message' => 'Contract request submitted successfully',
            'data' => $contract
        ], 201);
    }

    /**
     * Accept Price Offer
     */
    public function acceptOffer($id)
    {
        $user = auth()->user();
        if (!$user) return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);

        $contract = Contract::find($id);
        if (!$contract) return response()->json(['status' => false, 'message' => 'Contract not found'], 404);

        // Authorization check
        if ($contract->user_id != $user->id && $contract->corporate_customer_id != $user->corporateCustomer?->id) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
        }

        if ($contract->status !== 'offer_sent') {
            return response()->json(['status' => false, 'message' => 'Offer cannot be accepted in this state'], 422);
        }

        $contract->status = 'offer_accepted';
        $contract->save();

        // Notify Admins
        try {
            $admins = \App\Models\User::where('type', 'admin')->get();
            foreach ($admins as $admin) {
                $admin->sendFcmNotification([
                    'type' => 'contract_offer_accepted',
                    'title_ar' => 'قبول عرض السعر',
                    'title_en' => 'Price Offer Accepted',
                    'body_ar' => 'تم قبول عرض السعر للعقد رقم ' . $contract->contract_number,
                    'body_en' => 'The price offer for contract #' . $contract->contract_number . ' has been accepted.',
                    'data' => ['contract_id' => $contract->id]
                ], true);
            }
        } catch (\Exception $e) {
            \Log::error('FCM Error (contract_offer_accepted): ' . $e->getMessage());
        }

        return response()->json([
            'status' => true,
            'message' => 'Price offer accepted successfully',
            'data' => $contract
        ]);
    }

    /**
     * Reject Price Offer
     */
    public function rejectOffer(Request $request, $id)
    {
        $user = auth()->user();
        if (!$user) return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);

        $contract = Contract::find($id);
        if (!$contract) return response()->json(['status' => false, 'message' => 'Contract not found'], 404);

        // Authorization check
        if ($contract->user_id != $user->id && $contract->corporate_customer_id != $user->corporateCustomer?->id) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
        }

        if ($contract->status !== 'offer_sent') {
            return response()->json(['status' => false, 'message' => 'Offer cannot be rejected in this state'], 422);
        }

        $request->validate([
            'rejection_reason' => 'required|string|min:5',
        ]);

        $contract->status = 'offer_rejected';
        $contract->rejection_reason = $request->rejection_reason;
        $contract->save();

        // Notify Admins
        try {
            $admins = \App\Models\User::where('type', 'admin')->get();
            foreach ($admins as $admin) {
                $admin->sendFcmNotification([
                    'type' => 'contract_offer_rejected',
                    'title_ar' => 'رفض عرض السعر',
                    'title_en' => 'Price Offer Rejected',
                    'body_ar' => 'تم رفض عرض السعر للعقد رقم ' . $contract->contract_number,
                    'body_en' => 'The price offer for contract #' . $contract->contract_number . ' has been rejected.',
                    'data' => ['contract_id' => $contract->id]
                ], true);
            }
        } catch (\Exception $e) {
            \Log::error('FCM Error (contract_offer_rejected): ' . $e->getMessage());
        }

        return response()->json([
            'status' => true,
            'message' => 'Price offer rejected',
            'data' => $contract
        ]);
    }

    /**
     * Upload Signed Contract Copy
     */
    public function uploadSignedContract(Request $request, $id)
    {
        $user = auth()->user();
        if (!$user) return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);

        $contract = Contract::find($id);
        if (!$contract) return response()->json(['status' => false, 'message' => 'Contract not found'], 404);

        // Authorization check
        if ($contract->user_id != $user->id && $contract->corporate_customer_id != $user->corporateCustomer?->id) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
        }

        if (!in_array($contract->status, ['offer_accepted', 'awaiting_signed_contract', 'awaiting_payment_proof'])) {
            return response()->json(['status' => false, 'message' => 'Contract cannot be signed in this state'], 422);
        }

        $request->validate([
            'signed_contract_file' => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
        ]);

        $file = $request->file('signed_contract_file');
        $filename = time() . '_signed_' . $file->getClientOriginalName();
        $file->move(public_path('uploads/contracts/signed'), $filename);
        $contract->signed_contract_file = 'uploads/contracts/signed/' . $filename;

        // Transition status
        if ($contract->paymentReceipts()->exists()) {
            $contract->status = 'active'; // contracting completed
        } else {
            $contract->status = 'awaiting_payment_proof';
        }

        $contract->save();

        // Notify Admins
        try {
            $admins = \App\Models\User::where('type', 'admin')->get();
            foreach ($admins as $admin) {
                $admin->sendFcmNotification([
                    'type' => 'contract_signed_uploaded',
                    'title_ar' => 'عقد موقع جديد',
                    'title_en' => 'Signed Contract Uploaded',
                    'body_ar' => 'قام العميل برفع النسخة الموقعة من العقد رقم ' . $contract->contract_number,
                    'body_en' => 'The customer uploaded the signed copy of contract #' . $contract->contract_number,
                    'data' => ['contract_id' => $contract->id]
                ], true);
            }
        } catch (\Exception $e) {
            \Log::error('FCM Error (contract_signed_uploaded): ' . $e->getMessage());
        }

        return response()->json([
            'status' => true,
            'message' => 'Signed contract copy uploaded successfully',
            'data' => $contract
        ]);
    }

    /**
     * Upload Payment Receipt
     */
    public function uploadReceipt(Request $request, $id)
    {
        $user = auth()->user();
        if (!$user) return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);

        $contract = Contract::find($id);

        if (!$contract) {
            return response()->json(['status' => false, 'message' => 'Contract not found'], 404);
        }

        // Authorization check
        $canUpload = false;
        if ($user->type === 'maintenance_company' && $contract->maintenance_company_id == $user->maintenanceCompany?->id) $canUpload = true;
        if ($user->type === 'corporate_customer' && ($contract->corporate_customer_id == $user->corporateCustomer?->id || $contract->user_id == $user->id)) $canUpload = true;

        if (!$canUpload) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'receipt_file' => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
            'amount'       => 'required|numeric|min:0',
            'payment_date' => 'required|date',
            'notes'        => 'nullable|string',
        ]);

        $file = $request->file('receipt_file');
        $filename = time() . '_' . $file->getClientOriginalName();
        $file->move(public_path('uploads/contract_receipts'), $filename);
        $filePath = 'uploads/contract_receipts/' . $filename;

        $receipt = $contract->paymentReceipts()->create([
            'receipt_file' => $filePath,
            'amount'       => $request->amount,
            'payment_date' => $request->payment_date,
            'payment_method' => 'bank_transfer',
            'notes'        => $request->notes,
        ]);

        // Update financial totals
        $contract->paid_amount += $request->amount;
        $contract->remaining_amount = max(0, ($contract->project_value ?? 0) - $contract->paid_amount);

        // Transition status
        if ($contract->signed_contract_file) {
            $contract->status = 'active'; // Contracting completed
        } else {
            $contract->status = 'awaiting_signed_contract';
        }

        $contract->save();

        // Notify Admins
        try {
            $admins = \App\Models\User::where('type', 'admin')->get();
            foreach ($admins as $admin) {
                $admin->sendFcmNotification([
                    'type' => 'contract_receipt_uploaded',
                    'title_ar' => 'إثبات سداد عقد',
                    'title_en' => 'Contract Payment Receipt Uploaded',
                    'body_ar' => 'قام العميل برفع إثبات سداد دفعة للعقد رقم ' . $contract->contract_number,
                    'body_en' => 'The customer uploaded a payment receipt for contract #' . $contract->contract_number,
                    'data' => ['contract_id' => $contract->id]
                ], true);
            }
        } catch (\Exception $e) {
            \Log::error('FCM Error (contract_receipt_uploaded): ' . $e->getMessage());
        }

        return response()->json([
            'status' => true,
            'message' => 'Receipt uploaded successfully',
            'data' => $receipt->load('contract')
        ]);
    }

    /**
     * Upload Contract File (Main PDF template from Admin)
     */
    public function uploadContractFile(Request $request, $id)
    {
        $user = auth()->user();
        if (!$user) return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);

        $contract = Contract::find($id);

        if (!$contract) {
            return response()->json(['status' => false, 'message' => 'Contract not found'], 404);
        }

        // Authorization check (Company or Corporate Customer)
        $canUpload = false;
        if ($user->type === 'maintenance_company' && $contract->maintenance_company_id == $user->maintenanceCompany?->id) $canUpload = true;
        if ($user->type === 'corporate_customer' && ($contract->corporate_customer_id == $user->corporateCustomer?->id || $contract->user_id == $user->id)) $canUpload = true;
        if ($user->type === 'admin') $canUpload = true;

        if (!$canUpload) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'contract_file' => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
        ]);

        $file = $request->file('contract_file');
        $filename = time() . '_' . $file->getClientOriginalName();
        $file->move(public_path('uploads/contracts'), $filename);
        $filePath = 'uploads/contracts/' . $filename;

        $contract->contract_file = $filePath;
        $contract->save();

        return response()->json([
            'status' => true,
            'message' => 'Contract file uploaded successfully',
            'data' => $contract
        ]);
    }

    /**
     * Update Contract Details (Project Value, Contact Numbers, Files, and Receipts)
     */
    public function update(Request $request, $id)
    {
        $user = auth()->user();
        if (!$user) return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);

        $contract = Contract::find($id);

        if (!$contract) {
            return response()->json(['status' => false, 'message' => 'Contract not found'], 404);
        }

        // Authorization check
        $canUpdate = false;
        if ($user->type === 'maintenance_company' && $contract->maintenance_company_id == $user->maintenanceCompany?->id) $canUpdate = true;
        if ($user->type === 'corporate_customer' && ($contract->corporate_customer_id == $user->corporateCustomer?->id || $contract->user_id == $user->id)) $canUpdate = true;

        if (!$canUpdate) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'project_value'   => 'nullable|numeric|min:0',
            'contact_numbers' => 'nullable|array',
            'contact_numbers.*' => 'nullable|string|max:30',
            'contract_file'   => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
            // Optional Payment Receipt within the same update
            'receipt_file'    => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
            'receipt_amount'  => 'required_with:receipt_file|numeric|min:0',
            'receipt_date'    => 'required_with:receipt_file|date',
            'receipt_notes'   => 'nullable|string',
        ]);

        // 1. Update Basic Fields
        if ($request->has('project_value')) {
            $contract->project_value = $request->project_value;
        }
        
        if ($request->has('contact_numbers')) {
            $phones = array_filter($request->contact_numbers ?? []);
            $contract->contact_numbers = $phones ? implode(',', $phones) : null;
        }

        // 2. Handle Main Contract File Upload
        if ($request->hasFile('contract_file')) {
            $file = $request->file('contract_file');
            $filename = time() . '_contract_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/contracts'), $filename);
            $contract->contract_file = 'uploads/contracts/' . $filename;
        }

        // 3. Handle Payment Receipt Upload (New Record)
        if ($request->hasFile('receipt_file')) {
            $file = $request->file('receipt_file');
            $filename = time() . '_receipt_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/contract_receipts'), $filename);
            $receiptPath = 'uploads/contract_receipts/' . $filename;

            $contract->paymentReceipts()->create([
                'receipt_file'   => $receiptPath,
                'amount'         => $request->receipt_amount,
                'payment_date'   => $request->receipt_date,
                'payment_method' => 'bank_transfer',
                'notes'          => $request->receipt_notes,
            ]);

            // Update financial totals
            $contract->paid_amount += $request->receipt_amount;
        }

        // 4. Recalculate remaining amount and save
        $contract->remaining_amount = max(0, ($contract->project_value ?? 0) - $contract->paid_amount);
        $contract->save();

        return response()->json([
            'status' => true,
            'message' => 'Contract updated successfully',
            'data' => $contract->load('paymentReceipts')
        ]);
    }

    /**
     * Get the authenticated user's current/latest contract
     */
    public function myContract()
    {
        $user = auth()->user();
        \Log::info('myContract access attempt', [
            'user_id' => $user?->id,
            'has_token' => request()->bearerToken() ? 'yes' : 'no',
            'token' => request()->bearerToken() ? substr(request()->bearerToken(), 0, 10) . '...' : 'none',
            'headers' => request()->headers->all(),
        ]);
        if (!$user) return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);

        $query = Contract::with(['maintenanceCompany', 'paymentReceipts']);

        if ($user->type === 'maintenance_company') {
            $query->where('maintenance_company_id', $user->maintenanceCompany?->id);
        } elseif ($user->type === 'corporate_customer') {
            $query->where(function($q) use ($user) {
                $q->where('corporate_customer_id', $user->corporateCustomer?->id)
                  ->orWhere('user_id', $user->id);
            });
        } elseif ($user->type === 'technician') {
            $query->where('maintenance_company_id', $user->technician?->maintenance_company_id);
        } else {
            $query->where('user_id', $user->id);
        }

        $contract = $query->latest()->first();

        if (!$contract) {
            return response()->json(['status' => true, 'message' => 'No contract found for this user', 'data' => null], 200);
        }

        return response()->json(['status' => true, 'data' => $contract]);
    }

    /**
     * Update the authenticated user's current/latest contract
     */
    public function updateMyContract(Request $request)
    {
        $user = auth()->user();
        if (!$user) return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);

        $query = Contract::query();

        if ($user->type === 'maintenance_company') {
            $company = $user->maintenanceCompany;
            $query->where('maintenance_company_id', $company->id ?? 0);
        } elseif ($user->type === 'corporate_customer') {
            $corporate = $user->corporateCustomer;
            $query->where(function($q) use ($corporate, $user) {
                $q->where('corporate_customer_id', $corporate?->id ?? 0)
                  ->orWhere('user_id', $user->id);
            });
        } elseif ($user->type === 'technician') {
            $technician = $user->technician;
            $query->where('maintenance_company_id', $technician->maintenance_company_id ?? 0);
        } else {
            $query->where('user_id', $user->id);
        }

        $contract = $query->latest()->first();

        // If no contract exists, create a draft placeholder to allow data syncing
        if (!$contract) {
            $contractData = [
                'contract_number' => 'CNT-DRFT-' . strtoupper(uniqid()),
                'project_value'   => 0,
                'status'          => 'active',
                'user_id'         => $user->id
            ];

            if ($user->type === 'maintenance_company') {
                $contractData['maintenance_company_id'] = $user->maintenanceCompany?->id;
            } elseif ($user->type === 'corporate_customer') {
                $contractData['corporate_customer_id'] = $user->corporateCustomer?->id;
            } elseif ($user->type === 'technician') {
                $contractData['maintenance_company_id'] = $user->technician?->maintenance_company_id;
            }

            try {
                $contract = Contract::create($contractData);
            } catch (\Exception $e) {
                \Log::error('Failed to auto-create contract: ' . $e->getMessage());
                return response()->json(['status' => false, 'message' => 'No contract found and initialization failed'], 404);
            }
        }

        return $this->update($request, $contract->id);
    }
}

