<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;

class WalletTransactionController extends Controller
{
    /**
     * Display a listing of wallet transactions.
     */
    public function index(Request $request)
    {
        $query = WalletTransaction::with('user')->orderBy('created_at', 'desc');

        // Search
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($u) use ($search) {
                    $u->where('name', 'like', "%{$search}%");
                })->orWhere('transaction_number', 'like', "%{$search}%");
            });
        }

        // Filters
        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $items = $query->paginate(15);

        return view('admin.wallet_transactions.index', compact('items'));
    }

    /**
     * Display the specified wallet transaction.
     */
    public function show($id)
    {
        $item = WalletTransaction::with('user')->findOrFail($id);
        return view('admin.wallet_transactions.show', compact('item'));
    }
}
