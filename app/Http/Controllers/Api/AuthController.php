<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CorporateCustomer;
use App\Models\IndividualCustomer;
use App\Models\MaintenanceCompany;
use App\Models\Technician;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'type' => 'required|string|in:individual,corporate_company,technician,maintenance_company',
            'phone' => 'nullable|string',
            // Profile specific fields can be validated here or separately
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first(), 'errors' => $validator->errors()], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'type' => $request->type,
            'phone' => $request->phone,
        ]);

        // Create Profile based on type
        if ($request->type === 'individual') {
            IndividualCustomer::create([
                'user_id' => $user->id,
                'first_name_en' => $request->name,
                // Add other fields as needed from request
            ]);
        } elseif ($request->type === 'corporate_company') {
            CorporateCustomer::create([
                'user_id' => $user->id,
                'company_name_en' => $request->company_name ?? $request->name,
                // Add other fields
            ]);
        } elseif ($request->type === 'maintenance_company') {
            MaintenanceCompany::create([
                'user_id' => $user->id,
                'company_name_en' => $request->company_name ?? $request->name,
                // Add other fields
            ]);
        } elseif ($request->type === 'technician') {
            Technician::create([
                'user_id' => $user->id,
                // Add other fields
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => true,
            'message' => 'User registered successfully',
            'data' => [
                'user' => $user,
                'access_token' => $token,
                'token_type' => 'Bearer',
            ],
        ], 201);
    }

    public function login(Request $request)
    {
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid login details'
            ], 401);
        }

        $user = User::where('email', $request['email'])->firstOrFail();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => $user,
                'access_token' => $token,
                'token_type' => 'Bearer',
            ],
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => true,
            'message' => 'Logged out successfully'
        ]);
    }

    public function profile(Request $request)
    {
        $user = $request->user();
        
        // Load relationships based on type
        if ($user->type === 'individual') {
            $user->load('individualCustomer');
        } elseif ($user->type === 'corporate_company') {
            $user->load('corporateCustomer');
        } elseif ($user->type === 'maintenance_company') {
            $user->load('maintenanceCompany');
        } elseif ($user->type === 'technician') {
            $user->load('technician');
        } elseif ($user->type === 'admin') {
            // Admin does not have a separate profile relation currently
        }

        return response()->json([
            'status' => true,
            'message' => 'Profile retrieved successfully',
            'data' => $user
        ]);
    }
}
