<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\IndividualCustomer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class IndividualCustomerController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();
        if ($user->type !== 'individual') {
             return response()->json(['status' => false, 'message' => 'Unauthorized access'], 403);
        }

        $profile = IndividualCustomer::with('user')->where('user_id', $user->id)->first();
        if (!$profile) return response()->json(['status' => false, 'message' => 'Profile not found'], 404);
        return response()->json(['status' => true, 'message' => 'Profile retrieved', 'data' => $profile]);
    }

    public function update(Request $request)
    {
        $user = $request->user();
        if ($user->type !== 'individual') {
             return response()->json(['status' => false, 'message' => 'Unauthorized access'], 403);
        }

        $profile = IndividualCustomer::where('user_id', $user->id)->first();
        if (!$profile) return response()->json(['status' => false, 'message' => 'Profile not found'], 404);
        
        // Update User info
        if ($request->has('name')) $user->name = $request->name;
        if ($request->has('email')) $user->email = $request->email;
        if ($request->has('password') && $request->password) $user->password = Hash::make($request->password);
        if ($request->has('phone')) $user->phone = $request->phone;
        // Status typically shouldn't be self-updated to 'active' if suspended, but for now allowing or restricting? 
        // User updating their own status is usually rare unless "deactivating account". 
        // I will exclude 'status' and 'type' from self-update.
        $user->save();

        // Update Profile info
        $profile->update($request->except(['name', 'email', 'password', 'phone', 'status', 'type', 'user_id', 'id']));
        
        return response()->json(['status' => true, 'message' => 'Profile updated', 'data' => $profile->load('user')]);
    }
}
