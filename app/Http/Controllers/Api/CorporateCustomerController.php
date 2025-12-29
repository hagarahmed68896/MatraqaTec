<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CorporateCustomer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CorporateCustomerController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();
        if ($user->type !== 'corporate_company') {
             return response()->json(['status' => false, 'message' => 'Unauthorized access'], 403);
        }

        $profile = CorporateCustomer::with('user')->where('user_id', $user->id)->first();
        if (!$profile) return response()->json(['status' => false, 'message' => 'Profile not found'], 404);
        return response()->json(['status' => true, 'message' => 'Profile retrieved', 'data' => $profile]);
    }

    public function update(Request $request)
    {
        $user = $request->user();
        if ($user->type !== 'corporate_company') {
             return response()->json(['status' => false, 'message' => 'Unauthorized access'], 403);
        }

        $profile = CorporateCustomer::where('user_id', $user->id)->first();
        if (!$profile) return response()->json(['status' => false, 'message' => 'Profile not found'], 404);
        
        $user = $profile->user;
        if ($user) {
            if ($request->has('name')) $user->name = $request->name;
            if ($request->has('email')) $user->email = $request->email;
            if ($request->has('password') && $request->password) $user->password = Hash::make($request->password);
            if ($request->has('phone')) $user->phone = $request->phone;
            $user->save();
        }

        $profile->update($request->except(['name', 'email', 'password', 'phone', 'status', 'type', 'user_id', 'id']));
        
        return response()->json(['status' => true, 'message' => 'Profile updated', 'data' => $profile->load('user')]);
    }
}
