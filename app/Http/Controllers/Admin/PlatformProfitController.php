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
        $vat_rate = \App\Models\Setting::getByKey('vat_rate', 15);
        
        $stats = [
            'total_profit' => PlatformProfit::sum('amount'),
            'orders_count' => PlatformProfit::count(),
            'commission_rate' => $commission_rate,
            'vat_rate' => $vat_rate,
            'vat_rate_services' => \App\Models\Setting::getByKey('vat_rate_services', 15),
            'platform_commission_services' => \App\Models\Setting::getByKey('platform_commission_services', 15),
            'vat_rate_parts' => \App\Models\Setting::getByKey('vat_rate_parts', 15),
            'platform_commission_parts' => \App\Models\Setting::getByKey('platform_commission_parts', 15),
        ];

        return view('admin.platform_profits.index', compact('items', 'stats'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'vat_rate_services' => 'required|numeric|min:0|max:100',
            'platform_commission_services' => 'required|numeric|min:0|max:100',
            'vat_rate_parts' => 'required|numeric|min:0|max:100',
            'platform_commission_parts' => 'required|numeric|min:0|max:100',
        ]);

        \App\Models\Setting::setByKey('vat_rate_services', $request->vat_rate_services, 'platform');
        \App\Models\Setting::setByKey('platform_commission_services', $request->platform_commission_services, 'platform');
        \App\Models\Setting::setByKey('vat_rate_parts', $request->vat_rate_parts, 'platform');
        \App\Models\Setting::setByKey('platform_commission_parts', $request->platform_commission_parts, 'platform');

        // Backwards compatibility sync
        \App\Models\Setting::setByKey('platform_commission', $request->platform_commission_services, 'platform');
        \App\Models\Setting::setByKey('vat_rate', $request->vat_rate_services, 'platform');
        
        return redirect()->route('admin.platform-profits.index')->with('success', __('Commission and VAT settings updated successfully.'));
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
