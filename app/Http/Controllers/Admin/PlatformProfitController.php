<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlatformProfit;
use Illuminate\Http\Request;

class PlatformProfitController extends Controller
{
    public function index()
    {
        $fees = \App\Models\Setting::getByKey('platform_fees', 0);
        $profit = \App\Models\Setting::getByKey('platform_profit_value', 0);
        return view('admin.platform_profits.index', compact('fees', 'profit'));
    }

    public function store(Request $request)
    {
        \App\Models\Setting::setByKey('platform_fees', $request->fees, 'platform');
        \App\Models\Setting::setByKey('platform_profit_value', $request->amount, 'platform');
        
        return redirect()->route('admin.platform-profits.index')->with('success', __('Settings saved successfully.'));
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
