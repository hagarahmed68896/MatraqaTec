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
            'receipt_file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
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
            'payment_method' => 'bank_transfer', // Default or from request
            'notes'        => $request->notes,
        ]);

        // Update contract paid_amount and remaining_amount
        $contract->paid_amount += $request->amount;
        $contract->remaining_amount = max(0, $contract->project_value - $contract->paid_amount);
        $contract->save();

        return response()->json([
            'status' => true,
            'message' => 'Receipt uploaded successfully',
            'data' => $receipt->load('contract')
        ]);
    }

    /**
     * Upload Contract File (Main PDF)
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

        if (!$canUpload) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'contract_file' => 'required|file|mimes:pdf|max:10240',
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
            'contract_file'   => 'nullable|file|mimes:pdf|max:10240',
            // Optional Payment Receipt within the same update
            'receipt_file'    => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
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
        $contract->remaining_amount = max(0, $contract->project_value - $contract->paid_amount);
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
            return response()->json(['status' => false, 'message' => 'No contract found for this user'], 404);
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
            return response()->json(['status' => false, 'message' => 'No contract found to update'], 404);
        }

        return $this->update($request, $contract->id);
    }
}

