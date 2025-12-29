<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FinancialSettlement;
use Illuminate\Http\Request;

class FinancialSettlementController extends Controller
{
    public function index()
    {
        $settlements = FinancialSettlement::with('maintenanceCompany')
            ->where('user_id', auth()->id())
            ->get();
        return response()->json(['status' => true, 'message' => 'Settlements retrieved', 'data' => $settlements]);
    }

    public function store(Request $request)
    {
        $request->merge(['user_id' => auth()->id()]);
        $settlement = FinancialSettlement::create($request->all());
        return response()->json(['status' => true, 'message' => 'Settlement created', 'data' => $settlement]);
    }

    public function show($id)
    {
        $settlement = FinancialSettlement::where('user_id', auth()->id())->where('id', $id)->first();
        if (!$settlement) return response()->json(['status' => false, 'message' => 'Not found'], 404);
        return response()->json(['status' => true, 'message' => 'Settlement retrieved', 'data' => $settlement]);
    }
    
    // Update removed (Admin processes it)
}
