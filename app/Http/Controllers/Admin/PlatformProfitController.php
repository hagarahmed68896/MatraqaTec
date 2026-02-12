<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlatformProfit;
use Illuminate\Http\Request;

class PlatformProfitController extends Controller
{
    public function index()
    {
        $items = PlatformProfit::orderBy('created_at', 'desc')->paginate(15);
        return view('admin.platform_profits.index', compact('items'));
    }

    public function store(Request $request)
    {
        PlatformProfit::create($request->all());
        return redirect()->route('admin.platform-profits.index')->with('success', __('Profit recorded successfully.'));
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
