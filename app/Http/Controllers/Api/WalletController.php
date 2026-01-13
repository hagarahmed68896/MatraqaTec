<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WalletController extends Controller
{
    /**
     * Get user wallet balance and transaction history.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $transactions = WalletTransaction::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json([
            'status' => true,
            'message' => 'Wallet data retrieved successfully',
            'data' => [
                'balance' => $user->wallet_balance,
                'transactions' => $transactions,
            ],
        ]);
    }

    /**
     * Deposit funds to wallet (Simulated or via Payment Gateway).
     */
    public function deposit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:1',
            'payment_method' => 'required|string', // e.g., 'stripe', 'stc_pay'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }

        $user = $request->user();
        
        // In a real app, you'd process the payment here.
        // For now, we assume success.

        $amount = $request->amount;
        $user->wallet_balance += $amount;
        $user->save();

        WalletTransaction::create([
            'user_id' => $user->id,
            'amount' => $amount,
            'type' => 'deposit',
            'note' => "Deposit via {$request->payment_method}",
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Funds deposited successfully',
            'data' => [
                'balance' => $user->wallet_balance,
            ],
        ]);
    }
}
