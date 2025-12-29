<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use Illuminate\Http\Request;

class ContractController extends Controller
{
    public function index()
    {
        $contracts = Contract::with('maintenanceCompany')->get();
        return response()->json(['status' => true, 'message' => 'Contracts retrieved', 'data' => $contracts]);
    }

    public function store(Request $request)
    {
        $contract = Contract::create($request->all());
        return response()->json(['status' => true, 'message' => 'Contract created', 'data' => $contract]);
    }

    public function show($id)
    {
        $contract = Contract::with('maintenanceCompany')->find($id);
        if (!$contract) return response()->json(['status' => false, 'message' => 'Not found'], 404);
        return response()->json(['status' => true, 'message' => 'Contract retrieved', 'data' => $contract]);
    }

    public function update(Request $request, $id)
    {
        $contract = Contract::find($id);
        if (!$contract) return response()->json(['status' => false, 'message' => 'Not found'], 404);
        $contract->update($request->all());
        return response()->json(['status' => true, 'message' => 'Contract updated', 'data' => $contract]);
    }

    public function destroy($id)
    {
        $contract = Contract::find($id);
        if (!$contract) return response()->json(['status' => false, 'message' => 'Not found'], 404);
        $contract->delete();
        return response()->json(['status' => true, 'message' => 'Contract deleted']);
    }
}
