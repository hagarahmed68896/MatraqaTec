<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Technician;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class TechnicianController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();
        if ($user->type !== 'technician') {
             return response()->json(['status' => false, 'message' => 'Unauthorized access'], 403);
        }

        $technician = Technician::with('user', 'service', 'maintenanceCompany')->where('user_id', $user->id)->first();
        if (!$technician) return response()->json(['status' => false, 'message' => 'Technician not found'], 404);
        return response()->json(['status' => true, 'message' => 'Technician retrieved', 'data' => $technician]);
    }

    public function update(Request $request)
    {
        $user = $request->user();
        if ($user->type !== 'technician') {
             return response()->json(['status' => false, 'message' => 'Unauthorized access'], 403);
        }

        $technician = Technician::where('user_id', $user->id)->first();
        if (!$technician) return response()->json(['status' => false, 'message' => 'Technician not found'], 404);
        
        $user = $technician->user;
        if ($user) {
            if ($request->has('name')) $user->name = $request->name;
            if ($request->has('email')) $user->email = $request->email;
            if ($request->has('password') && $request->password) $user->password = Hash::make($request->password);
            if ($request->has('phone')) $user->phone = $request->phone;
            $user->save();
        }

        $technician->update($request->except(['name', 'email', 'password', 'phone', 'status', 'type', 'user_id', 'id']));
        
        return response()->json(['status' => true, 'message' => 'Technician updated', 'data' => $technician->load('user')]);
    }
}
