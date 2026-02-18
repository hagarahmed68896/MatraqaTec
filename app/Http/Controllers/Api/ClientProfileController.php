<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ClientProfileController extends Controller
{
    use \App\Traits\HandlesLocation;

    /**
     * Get Client Profile (Main Screen + Info Screen data)
     */
    public function show(Request $request)
    {
        $user = $request->user();
        
        // Ensure user is an individual or corporate customer
        if (!in_array($user->type, ['individual', 'corporate_customer'])) {
             return response()->json(['status' => false, 'message' => 'Unauthorized access'], 403);
        }

        $user->load(['individualCustomer', 'corporateCustomer', 'favorites', 'searchHistories']); 

        // Calculate some stats if needed for the dashboard (like order count)
        $ordersCount = \App\Models\Order::where('user_id', $user->id)->count();
        $walletBalance = $user->wallet_balance;

        return response()->json([
            'status' => true,
            'message' => 'Profile retrieved successfully',
            'data' => [
                'user' => $user,
                'stats' => [
                    'orders_count' => $ordersCount,
                    'wallet_balance' => $walletBalance
                ]
            ]
        ]);
    }

    /**
     * Update Profile Info (بياناتي)
     */
    public function update(Request $request)
    {
        $user = $request->user();
        
        $validator = Validator::make($request->all(), [
            'name'          => 'nullable|string|max:255',
            'email'         => 'nullable|email|unique:users,email,' . $user->id,
            'phone'         => 'nullable|string|regex:/^5[0-9]{8}$/|unique:users,phone,' . $user->id,
            'avatar'        => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'address'       => 'nullable|string|max:255',
            'latitude'      => 'nullable|numeric|between:-90,90',
            'longitude'     => 'nullable|numeric|between:-180,180',
            // Corporate specific
            'commercial_record_number' => 'nullable|string|max:255',
            'tax_number' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }

        // Update main name
        if ($request->filled('name')) {
            $user->name = $request->name;
        }

        // Sanitize phone if present
        if ($request->has('phone')) {
             $phone = $request->phone;
             $phone = preg_replace('/[^0-9]/', '', $phone);
             if (str_starts_with($phone, '966')) $phone = substr($phone, 3);
             if (str_starts_with($phone, '0')) $phone = substr($phone, 1);
             $user->phone = $phone;
        }

        if ($request->has('email')) $user->email = $request->email;
        
        // Location Logic: Tie Lat/Lng with Address & City
        if ($request->has('latitude') && $request->has('longitude')) {
             $user->latitude = $request->latitude;
             $user->longitude = $request->longitude;

             // Fetch location data from OSM
             $locationData = $this->getLocationDataFromCoords($request->latitude, $request->longitude);
             
             if ($locationData) {
                 // 1. Auto-fill Address if not provided
                 if (!$request->filled('address') && !empty($locationData['display_name'])) {
                     $user->address = $locationData['display_name'];
                 }

                 // 2. Auto-detect City and verify against DB
                 if (!empty($locationData['city_name'])) {
                    $cityName = $locationData['city_name'];
                    // Try to find matching city in DB (Arabic or English)
                    $city = \App\Models\City::where('name_ar', 'LIKE', "%{$cityName}%")
                                            ->orWhere('name_en', 'LIKE', "%{$cityName}%")
                                            ->first();
                    if ($city) {
                        $user->city_id = $city->id;
                    }
                 }
             }
        }
        
        // If address is explicitly provided, it overrides everything
        if ($request->filled('address')) {
            $user->address = $request->address;
        }
        
        // Handle Avatar Upload
        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::delete($user->avatar); // Delete old avatar
            }
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $path;
        }

        $user->save();

        // Update Related Profile Tables for consistency
        if ($user->type === 'individual' && $user->individualCustomer) {
            $user->individualCustomer()->update([
                'first_name_ar' => $user->name,
                'first_name_en' => $user->name,
            ]);
        } elseif ($user->type === 'corporate_customer' && $user->corporateCustomer) {
            $user->corporateCustomer()->update([
                'company_name_ar' => $user->name,
                'company_name_en' => $user->name,
                'commercial_record_number' => $request->commercial_record_number ?? $user->corporateCustomer->commercial_record_number,
                'tax_number' => $request->tax_number ?? $user->corporateCustomer->tax_number,
                'address' => $user->address,
            ]);
        }

        $user->refresh();

        return response()->json([
            'status' => true,
            'message' => 'Profile updated successfully',
            'data' => $user->load(['individualCustomer', 'corporateCustomer'])
        ]);
    }

    /**
     * Change Password (كلمة المرور)
     */
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['status' => false, 'message' => 'Current password is incorrect'], 422);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'Password updated successfully'
        ]);
    }

    /**
     * Update Location from determination popup (السماح)
     */
    public function updateLocation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'latitude'  => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }

        $user = $request->user();
        $user->latitude = $request->latitude;
        $user->longitude = $request->longitude;

        // Auto-detect address and city if possible
        $locationData = $this->getLocationDataFromCoords($request->latitude, $request->longitude);
        if ($locationData) {
            if (!empty($locationData['display_name'])) {
                $user->address = $locationData['display_name'];
            }
            if (!empty($locationData['city_name'])) {
                $city = \App\Models\City::where('name_ar', 'LIKE', "%{$locationData['city_name']}%")
                                        ->orWhere('name_en', 'LIKE', "%{$locationData['city_name']}%")
                                        ->first();
                if ($city) {
                    $user->city_id = $city->id;
                }
            }
        }

        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'Location updated successfully',
            'data' => [
                'latitude' => $user->latitude,
                'longitude' => $user->longitude,
                'address' => $user->address,
                'city_id' => $user->city_id
            ]
        ]);
    }
}
