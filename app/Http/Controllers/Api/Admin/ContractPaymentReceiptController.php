<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\ContractPaymentReceipt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ContractPaymentReceiptController extends Controller
{
    /**
     * List all payment receipts for a specific contract
     */
    public function index($contractId)
    {
        $contract = Contract::find($contractId);
        if (!$contract) {
            return response()->json(['status' => false, 'message' => 'Contract not found'], 404);
        }

        $receipts = ContractPaymentReceipt::where('contract_id', $contractId)
            ->orderBy('payment_date', 'desc')
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Payment receipts retrieved',
            'data' => $receipts
        ]);
    }

    /**
     * Upload a new payment receipt
     */
    public function store(Request $request, $contractId)
    {
        // Debug: Log to verify this controller is being called
        \Log::info('ContractPaymentReceiptController@store called', [
            'contract_id' => $contractId,
            'request_data' => $request->except('receipt_file')
        ]);

        $contract = Contract::find($contractId);
        if (!$contract) {
            return response()->json(['status' => false, 'message' => 'Contract not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'receipt_file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120', // 5MB max
            'amount' => 'required|numeric|min:0',
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:bank_transfer,cash,check,other',
            'reference_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Upload the receipt file
            $file = $request->file('receipt_file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('contract_receipts', $filename, 'public');

            // Create the payment receipt record
            $receipt = ContractPaymentReceipt::create([
                'contract_id' => $contractId,
                'receipt_file' => $path,
                'amount' => $request->amount,
                'payment_date' => $request->payment_date,
                'payment_method' => $request->payment_method,
                'reference_number' => $request->reference_number,
                'notes' => $request->notes,
            ]);

            // Update contract paid_amount and remaining_amount
            $contract->paid_amount = $contract->paymentReceipts()->sum('amount');
            $contract->remaining_amount = $contract->project_value - $contract->paid_amount;
            $contract->save();

            return response()->json([
                'status' => true,
                'message' => 'Payment receipt uploaded successfully',
                'data' => $receipt
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Upload failed',
                'error_detail' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * View a specific payment receipt
     */
    public function show($contractId, $id)
    {
        $receipt = ContractPaymentReceipt::where('contract_id', $contractId)
            ->where('id', $id)
            ->first();

        if (!$receipt) {
            return response()->json(['status' => false, 'message' => 'Receipt not found'], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Payment receipt retrieved',
            'data' => $receipt
        ]);
    }

    /**
     * Delete a payment receipt
     */
    public function destroy($contractId, $id)
    {
        $receipt = ContractPaymentReceipt::where('contract_id', $contractId)
            ->where('id', $id)
            ->first();

        if (!$receipt) {
            return response()->json(['status' => false, 'message' => 'Receipt not found'], 404);
        }

        try {
            // Delete the file from storage
            if (Storage::disk('public')->exists($receipt->receipt_file)) {
                Storage::disk('public')->delete($receipt->receipt_file);
            }

            // Get the contract before deleting receipt
            $contract = Contract::find($contractId);

            // Delete the receipt record
            $receipt->delete();

            // Update contract paid_amount and remaining_amount
            if ($contract) {
                $contract->paid_amount = $contract->paymentReceipts()->sum('amount');
                $contract->remaining_amount = $contract->project_value - $contract->paid_amount;
                $contract->save();
            }

            return response()->json([
                'status' => true,
                'message' => 'Payment receipt deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Delete failed',
                'error_detail' => $e->getMessage()
            ], 500);
        }
    }
}
