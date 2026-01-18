<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WalletController extends Controller
{
    /**
     * Get user wallet balance and transaction history with filters.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $query = WalletTransaction::where('user_id', $user->id);

        // Filter by Type (شحن/خصم)
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by Time Range
        if ($request->filled('time_filter')) {
            switch ($request->time_filter) {
                case 'yesterday': // أمس
                    $query->whereDate('created_at', now()->subDay()->toDateString());
                    break;
                case 'last_month': // الشهر الماضي
                    $query->whereMonth('created_at', now()->subMonth()->month)
                          ->whereYear('created_at', now()->subMonth()->year);
                    break;
                case 'last_3_months': // آخر 3 شهور
                    $query->where('created_at', '>=', now()->subMonths(3));
                    break;
                case 'custom': // الفترة المخصصة
                    if ($request->filled('start_date') && $request->filled('end_date')) {
                        $query->whereBetween('created_at', [
                            $request->start_date,
                            $request->end_date . ' 23:59:59'
                        ]);
                    }
                    break;
            }
        }

        // Sorting (ترتيب)
        if ($request->filled('sort_by')) {
            switch ($request->sort_by) {
                case 'newest': // الأحدث
                    $query->orderBy('created_at', 'desc');
                    break;
                case 'oldest': // الأقدم
                    $query->orderBy('created_at', 'asc');
                    break;
                case 'highest_amount': // الأعلى سعرًا
                    $query->orderBy('amount', 'desc');
                    break;
                case 'lowest_amount': // الأقل سعرًا
                    $query->orderBy('amount', 'asc');
                    break;
                case 'name': // ترتيب بالاسم (by note/description)
                    $query->orderBy('note', 'asc');
                    break;
                default:
                    $query->orderBy('created_at', 'desc');
            }
        } else {
            $query->orderBy('created_at', 'desc'); // Default: newest first
        }

        $transactions = $query->with('order.service')->paginate(15);

        // Add Arabic labels and enhanced details to transactions
        $transactions->getCollection()->transform(function ($transaction) {
            $typeLabels = [
                'deposit' => 'شحن',
                'debit' => 'خصم',
                'refund' => 'استرداد',
                'payment' => 'دفع'
            ];
            
            $transaction->type_label = $typeLabels[$transaction->type] ?? $transaction->type;
            
            // Format amount with sign
            $sign = in_array($transaction->type, ['debit', 'payment']) ? '-' : '+';
            $transaction->formatted_amount = $sign . ' ₪' . number_format($transaction->amount, 0);
            
            // Enhanced transaction details
            $transaction->display_title = $transaction->note ?? 'معاملة';
            $transaction->display_description = '';
            $transaction->reference_number = null;
            
            // If transaction is related to an order
            if ($transaction->reference_type === 'App\Models\Order' && $transaction->order) {
                $order = $transaction->order;
                $serviceName = $order->service->name_ar ?? 'خدمة';
                $categoryName = $order->service->parent->name_ar ?? '';
                
                $transaction->display_title = $serviceName . ($categoryName ? " ({$categoryName})" : '');
                
                if ($transaction->type === 'payment') {
                    $transaction->display_description = 'دفع خدمة';
                } elseif ($transaction->type === 'refund') {
                    $transaction->display_description = 'استرجاع';
                }
                
                $transaction->reference_number = 'رقم ' . $order->id;
            } else {
                // Generic wallet transactions
                $transaction->display_description = $transaction->type_label;
            }
            
            $transaction->formatted_date = $transaction->created_at->format('d/m/Y');
            
            return $transaction;
        });

        return response()->json([
            'status' => true,
            'message' => 'Wallet data retrieved successfully',
            'data' => [
                'balance' => number_format($user->wallet_balance, 2),
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
