<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use Illuminate\Http\Request;

class ContractController extends Controller
{
    public function index()
    {
        // Scope to authenticated user (technician or company)
        $user = auth()->user();
        if (!$user) return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
        
        // Assuming contracts linked via user_id or technician_id. 
        $contracts = Contract::with('maintenanceCompany')
            ->where('user_id', $user->id) // or technician_id depending on schema. Assuming user_id works for general cases.
            ->get();
            
        return response()->json(['status' => true, 'message' => 'Contracts retrieved', 'data' => $contracts]);
    }

    public function show($id)
    {
        $contract = Contract::with('maintenanceCompany')
            ->where('user_id', auth()->id())
            ->where('id', $id)
            ->first();
            
        if (!$contract) return response()->json(['status' => false, 'message' => 'Not found'], 404);
        return response()->json(['status' => true, 'message' => 'Contract retrieved', 'data' => $contract]);
    }
    
}
