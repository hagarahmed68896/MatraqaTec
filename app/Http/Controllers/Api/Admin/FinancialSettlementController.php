<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\FinancialSettlement;
use Illuminate\Http\Request;

class FinancialSettlementController extends Controller
{
    public function index()
    {
        $settlements = FinancialSettlement::with('maintenanceCompany')->get();
        return response()->json(['status' => true, 'message' => 'Settlements retrieved', 'data' => $settlements]);
    }

    public function store(Request $request)
    {
        $settlement = FinancialSettlement::create($request->all());
        return response()->json(['status' => true, 'message' => 'Settlement created', 'data' => $settlement]);
    }

    public function show($id)
    {
        $settlement = FinancialSettlement::find($id);
        if (!$settlement) return response()->json(['status' => false, 'message' => 'Not found'], 404);
        return response()->json(['status' => true, 'message' => 'Settlement retrieved', 'data' => $settlement]);
    }

    public function update(Request $request, $id)
    {
        $settlement = FinancialSettlement::find($id);
        if (!$settlement) return response()->json(['status' => false, 'message' => 'Not found'], 404);
        $settlement->update($request->all());
        return response()->json(['status' => true, 'message' => 'Settlement updated', 'data' => $settlement]);
    }

    public function destroy($id)
    {
        $settlement = FinancialSettlement::find($id);
        if (!$settlement) return response()->json(['status' => false, 'message' => 'Not found'], 404);
        $settlement->delete();
        return response()->json(['status' => true, 'message' => 'Settlement deleted']);
    }
}
