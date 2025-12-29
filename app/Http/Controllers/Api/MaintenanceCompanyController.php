<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceCompany;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class MaintenanceCompanyController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();
        if ($user->type !== 'maintenance_company') {
             return response()->json(['status' => false, 'message' => 'Unauthorized access'], 403);
        }

        $company = MaintenanceCompany::with('technicians')->where('user_id', $user->id)->first();
        if (!$company) return response()->json(['status' => false, 'message' => 'Company not found'], 404);
        return response()->json(['status' => true, 'message' => 'Company retrieved', 'data' => $company]);
    }

    public function update(Request $request)
    {
        $user = $request->user();
        if ($user->type !== 'maintenance_company') {
             return response()->json(['status' => false, 'message' => 'Unauthorized access'], 403);
        }

        $company = MaintenanceCompany::where('user_id', $user->id)->first();
        if (!$company) return response()->json(['status' => false, 'message' => 'Company not found'], 404);
        
        $user = $company->user;
        if ($user) {
            if ($request->has('name')) $user->name = $request->name;
            if ($request->has('email')) $user->email = $request->email;
            if ($request->has('password') && $request->password) $user->password = Hash::make($request->password);
            if ($request->has('phone')) $user->phone = $request->phone;
            $user->save();
        }

        $company->update($request->except(['name', 'email', 'password', 'phone', 'status', 'type', 'user_id', 'id']));
        
        return response()->json(['status' => true, 'message' => 'Company updated', 'data' => $company->load('user')]);
    }
}
