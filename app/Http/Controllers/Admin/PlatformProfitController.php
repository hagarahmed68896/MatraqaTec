<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlatformProfit;
use Illuminate\Http\Request;

class PlatformProfitController extends Controller
{
    public function index()
    {
        $items = PlatformProfit::with('order.user')->latest()->paginate(10);
        $commission_rate = \App\Models\Setting::getByKey('platform_commission', 15);
        
        $stats = [
            'total_profit' => PlatformProfit::sum('amount'),
            'orders_count' => PlatformProfit::count(),
            'commission_rate' => $commission_rate
        ];

        return view('admin.platform_profits.index', compact('items', 'stats'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'commission_rate' => 'required|numeric|min:0|max:100',
        ]);

        \App\Models\Setting::setByKey('platform_commission', $request->commission_rate, 'platform');
        
        return redirect()->route('admin.platform-profits.index')->with('success', __('Commission rate updated successfully.'));
    }

    public function show($id)
    {
        $item = PlatformProfit::findOrFail($id);
        return view('admin.platform_profits.show', compact('item'));
    }

    public function destroy($id)
    {
        $profit = PlatformProfit::findOrFail($id);
        $profit->delete();
        return redirect()->route('admin.platform-profits.index')->with('success', __('Profit deleted successfully.'));
    }
}
