<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlatformProfit;
use Illuminate\Http\Request;

class PlatformProfitController extends Controller
{
    public function index()
    {
        $profits = PlatformProfit::get();
        return response()->json(['status' => true, 'message' => 'Profits retrieved', 'data' => $profits]);
    }

    public function store(Request $request)
    {
        $profit = PlatformProfit::create($request->all());
        return response()->json(['status' => true, 'message' => 'Profit recorded', 'data' => $profit]);
    }

    public function show($id)
    {
        $profit = PlatformProfit::find($id);
        if (!$profit) return response()->json(['status' => false, 'message' => 'Not found'], 404);
        return response()->json(['status' => true, 'message' => 'Profit retrieved', 'data' => $profit]);
    }

    public function destroy($id)
    {
        $profit = PlatformProfit::find($id);
        if (!$profit) return response()->json(['status' => false, 'message' => 'Not found'], 404);
        $profit->delete();
        return response()->json(['status' => true, 'message' => 'Profit deleted']);
    }
}
