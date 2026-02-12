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
use Carbon\Carbon;

class AuthController extends Controller
{
    //todo:otp from client

    public function register(Request $request)
    {
        if ($request->has('phone')) {
            $phone = $request->phone;
            $phone = preg_replace('/[^0-9]/', '', $phone);
            if (str_starts_with($phone, '966')) $phone = substr($phone, 3);
            if (str_starts_with($phone, '0')) $phone = substr($phone, 1);
            $request->merge(['phone' => $phone]);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required_if:type,individual,technician|nullable|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'type' => 'required|string|in:individual,technician,maintenance_company,corporate_customer', 
            'phone' => 'required|string|regex:/^5[0-9]{8}$/|unique:users',
            
            // Company Specific Validations (Both Maintenance and Corporate)
            'company_name' => 'required_if:type,maintenance_company,corporate_customer|nullable|string|max:255',
            'commercial_record_number' => 'required_if:type,maintenance_company,corporate_customer|nullable|string|max:255',
            'commercial_record_file' => 'required_if:type,maintenance_company,corporate_customer|nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'tax_number' => 'required_if:type,maintenance_company,corporate_customer|nullable|string|max:255',
            'address' => 'required_if:type,maintenance_company,corporate_customer|nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first(), 'errors' => $validator->errors()], 422);
        }

        // Logic: For companies, use company_name as User.name. For others, use name.
        $accountName = in_array($request->type, ['maintenance_company', 'corporate_customer']) 
            ? $request->company_name 
            : $request->name;

        $user = User::create([
            'name' => $accountName,
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
                'first_name_ar' => $request->name_ar ?? $request->name,
            ]);
        } elseif ($request->type === 'maintenance_company') {
            // Handle CR File Upload
            $crFilePath = null;
            if ($request->hasFile('commercial_record_file')) {
                $crFilePath = $request->file('commercial_record_file')->store('commercial_records', 'public');
            }

            // Create MaintenanceCompany (as Provider/Company Account)
            MaintenanceCompany::create([
                'user_id' => $user->id,
                'company_name_en' => $request->company_name, 
                'company_name_ar' => $request->company_name_ar ?? $request->company_name,
                'commercial_record_number' => $request->commercial_record_number,
                'commercial_record_file' => $crFilePath,
                'tax_number' => $request->tax_number,
                'address' => $request->address,
                'city_id' => $request->city_id ?? 1, 
            ]);
        } elseif ($request->type === 'corporate_customer') {
            // Handle CR File Upload
            $crFilePath = null;
            if ($request->hasFile('commercial_record_file')) {
                $crFilePath = $request->file('commercial_record_file')->store('commercial_records', 'public');
            }

            // Create CorporateCustomer (as Service Requester Company)
            CorporateCustomer::create([
                'user_id' => $user->id,
                'company_name_en' => $request->company_name, 
                'company_name_ar' => $request->company_name_ar ?? $request->company_name,
                'commercial_record_number' => $request->commercial_record_number,
                'commercial_record_file' => $crFilePath,
                'tax_number' => $request->tax_number,
                'address' => $request->address,
            ]);
        } elseif ($request->type === 'technician') {
            Technician::create([
                'user_id' => $user->id,
            ]);
        }

        $this->sendOtp($user);

        return response()->json([
            'status' => true,
            'message' => 'User registered successfully. Please verify your phone with OTP.',
            'data' => [
                'user' => $user->load(['maintenanceCompany', 'corporateCustomer']), 
            ],
        ], 200);
    }

    public function login(Request $request)
    {
        if ($request->filled('phone')) {
            $phone = $request->phone;
            $phone = preg_replace('/[^0-9]/', '', $phone);
            if (str_starts_with($phone, '966')) $phone = substr($phone, 3);
            if (str_starts_with($phone, '0')) $phone = substr($phone, 1);
            $request->merge(['phone' => $phone]);
        }

        $validator = Validator::make($request->all(), [
            'email' => 'required_without:phone|email',
            'phone' => 'required_without:email|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }

        $credentials = ['password' => $request->password];
        if ($request->filled('email')) {
            $credentials['email'] = $request->email;
        } elseif ($request->filled('phone')) {
            $credentials['phone'] = $request->phone;
        } else {
             return response()->json(['status' => false, 'message' => 'Email or Phone is required'], 422);
        }

        if (!Auth::attempt($credentials)) {
            // Debug: Check if user exists but password failed
            $userExists = User::where($request->filled('email') ? 'email' : 'phone', $request->filled('email') ? $request->email : $request->phone)->exists();
            
            return response()->json([
                'status' => false,
                'message' => 'Invalid login details',
                'debug' => $userExists ? 'User exists but password incorrect' : 'User not found'
            ], 401);
        }

        $user = Auth::user();
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

    public function verifyOtp(Request $request)
    {
        if ($request->has('phone')) {
            $phone = $request->phone;
            $phone = preg_replace('/[^0-9]/', '', $phone);
            if (str_starts_with($phone, '966')) $phone = substr($phone, 3);
            if (str_starts_with($phone, '0')) $phone = substr($phone, 1);
            $request->merge(['phone' => $phone]);
        }

        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
            'otp' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }

        $user = User::where('phone', $request->phone)->first();

        if (!$user || $user->otp !== $request->otp || Carbon::now()->gt($user->otp_expires_at)) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid or expired OTP'
            ], 400);
        }

        // Clear OTP
        $user->otp = null;
        $user->otp_expires_at = null;
        $user->status = 'active'; 
        $user->save();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => true,
            'message' => 'Phone verified successfully',
            'data' => [
                'user' => $user,
                'access_token' => $token,
                'token_type' => 'Bearer',
            ],
        ]);
    }

    public function resendOtp(Request $request)
    {
        if ($request->has('phone')) {
            $phone = $request->phone;
            $phone = preg_replace('/[^0-9]/', '', $phone);
            if (str_starts_with($phone, '966')) $phone = substr($phone, 3);
            if (str_starts_with($phone, '0')) $phone = substr($phone, 1);
            $request->merge(['phone' => $phone]);
        }

        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }

        $user = User::where('phone', $request->phone)->first();

        if (!$user) {
            return response()->json(['status' => false, 'message' => 'User not found'], 404);
        }

        $this->sendOtp($user);

        return response()->json([
            'status' => true,
            'message' => 'OTP sent successfully',
        ]);
    }

    public function forgotPassword(Request $request)
    {
        if ($request->has('phone')) {
            $phone = $request->phone;
            $phone = preg_replace('/[^0-9]/', '', $phone);
            if (str_starts_with($phone, '966')) $phone = substr($phone, 3);
            if (str_starts_with($phone, '0')) $phone = substr($phone, 1);
            $request->merge(['phone' => $phone]);
        }

        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|exists:users,phone',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => 'Phone number not found'], 404);
        }

        $user = User::where('phone', $request->phone)->first();
        $this->sendOtp($user);

        return response()->json([
            'status' => true,
            'message' => 'OTP for password reset sent to your phone',
        ]);
    }

    public function resetPassword(Request $request)
    {
        if ($request->has('phone')) {
            $phone = $request->phone;
            $phone = preg_replace('/[^0-9]/', '', $phone);
            if (str_starts_with($phone, '966')) $phone = substr($phone, 3);
            if (str_starts_with($phone, '0')) $phone = substr($phone, 1);
            $request->merge(['phone' => $phone]);
        }

        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|exists:users,phone',
            'otp' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }

        $user = User::where('phone', $request->phone)->first();

    // TEMPORARY: Allow "0000" as a bypass for OTP verification during testing/development
    $isTemporaryOtp = ($request->otp === "0000");

    if (!$isTemporaryOtp && ($user->otp !== $request->otp || Carbon::now()->gt($user->otp_expires_at))) {
        return response()->json([
                'status' => false,
                'message' => 'Invalid or expired OTP'
            ], 400);
        }

        $user->password = Hash::make($request->password);
        $user->otp = null;
        $user->otp_expires_at = null;
        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'Password reset successfully. You can now login with your new password.',
        ]);
    }

    private function sendOtp($user)
    {
        $otp = "0000";
        $user->otp = $otp;
        $user->otp_expires_at = Carbon::now()->addMinutes(10);
        $user->save();

        //  may would integrate an SMS gateway here.
        // For now, we will just log it.
        \Log::info("OTP for user {$user->phone}: {$otp}");
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
        
        if ($user->type === 'individual') {
            $user->load('individualCustomer');
        } elseif ($user->type === 'maintenance_company') {
            $user->load('maintenanceCompany');
        } elseif ($user->type === 'corporate_customer') {
            $user->load('corporateCustomer');
        } elseif ($user->type === 'technician') {
            $user->load('technician');
        }

        return response()->json([
            'status' => true,
            'message' => 'Profile retrieved successfully',
            'data' => $user
        ]);
    }
}
