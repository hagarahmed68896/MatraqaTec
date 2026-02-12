<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\ContractPaymentReceipt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class ContractPaymentReceiptController extends Controller
{
    /**
     * List all payment receipts for a specific contract
     */
    public function index($contractId)
    {
        $contract = Contract::findOrFail($contractId);
        $items = ContractPaymentReceipt::where('contract_id', $contractId)
            ->orderBy('payment_date', 'desc')
            ->paginate(15);

        return view('admin.contract_payment_receipts.index', compact('contract', 'items'));
    }

    /**
     * Show form to upload a new payment receipt
     */
    public function create($contractId)
    {
        $contract = Contract::findOrFail($contractId);
        return view('admin.contract_payment_receipts.create', compact('contract'));
    }

    /**
     * Upload a new payment receipt
     */
    public function store(Request $request, $contractId)
    {
        $contract = Contract::findOrFail($contractId);

        $validator = Validator::make($request->all(), [
            'receipt_file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'amount' => 'required|numeric|min:0',
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:bank_transfer,cash,check,other',
            'reference_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $file = $request->file('receipt_file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('contract_receipts', $filename, 'public');

            ContractPaymentReceipt::create([
                'contract_id' => $contractId,
                'receipt_file' => $path,
                'amount' => $request->amount,
                'payment_date' => $request->payment_date,
                'payment_method' => $request->payment_method,
                'reference_number' => $request->reference_number,
                'notes' => $request->notes,
            ]);

            $contract->paid_amount = $contract->paymentReceipts()->sum('amount');
            $contract->remaining_amount = $contract->project_value - $contract->paid_amount;
            $contract->save();

            return redirect()->route('admin.contracts.payment-receipts.index', $contractId)->with('success', __('Payment receipt uploaded successfully.'));

        } catch (\Exception $e) {
            Log::error('Upload failed: ' . $e->getMessage());
            return back()->with('error', __('Upload failed: ') . $e->getMessage());
        }
    }

    /**
     * View a specific payment receipt
     */
    public function show($contractId, $id)
    {
        $item = ContractPaymentReceipt::where('contract_id', $contractId)->findOrFail($id);
        $contract = Contract::findOrFail($contractId);
        return view('admin.contract_payment_receipts.show', compact('item', 'contract'));
    }

    /**
     * Delete a payment receipt
     */
    public function destroy($contractId, $id)
    {
        $receipt = ContractPaymentReceipt::where('contract_id', $contractId)->findOrFail($id);

        try {
            if (Storage::disk('public')->exists($receipt->receipt_file)) {
                Storage::disk('public')->delete($receipt->receipt_file);
            }

            $contract = Contract::findOrFail($contractId);
            $receipt->delete();

            $contract->paid_amount = $contract->paymentReceipts()->sum('amount');
            $contract->remaining_amount = $contract->project_value - $contract->paid_amount;
            $contract->save();

            return redirect()->route('admin.contracts.payment-receipts.index', $contractId)->with('success', __('Payment receipt deleted successfully.'));

        } catch (\Exception $e) {
            Log::error('Delete failed: ' . $e->getMessage());
            return back()->with('error', __('Delete failed: ') . $e->getMessage());
        }
    }
}
