<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ClientProfileController extends Controller
{
    /**
     * Get Client Profile (Main Screen + Info Screen data)
     */
    public function show(Request $request)
    {
        $user = $request->user();
        
        // Ensure user is an individual customer
        if ($user->type !== 'individual' && $user->type !== 'corporate_company') {
            // Adjust logic if you want to support other types or restrict strictly
             // based on "Client User" request, usually implies Individual/Corporate
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
            'first_name_ar' => 'nullable|string|max:255',
            'first_name_en' => 'nullable|string|max:255',
            'last_name_ar'  => 'nullable|string|max:255',
            'last_name_en'  => 'nullable|string|max:255',
            'email'         => 'nullable|email|unique:users,email,' . $user->id,
            'phone'         => 'nullable|string|regex:/^5[0-9]{8}$/|unique:users,phone,' . $user->id,
            'avatar'        => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'address'       => 'nullable|string|max:255',
            'latitude'      => 'nullable|numeric|between:-90,90',
            'longitude'     => 'nullable|numeric|between:-180,180',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
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

        // Update Related Profile
        if ($user->type === 'individual') {
            $user->individualCustomer()->update([
                'first_name_ar' => $request->first_name_ar ?? $user->individualCustomer->first_name_ar,
                'first_name_en' => $request->first_name_en ?? $user->individualCustomer->first_name_en,
                'last_name_ar'  => $request->last_name_ar ?? $user->individualCustomer->last_name_ar,
                'last_name_en'  => $request->last_name_en ?? $user->individualCustomer->last_name_en,
            ]);
            
            // Name update logic for User table (Main display name)
            // Strategy: Use First Name + Last Name (English/Arabic based on logic or just update name column)
            // Usually 'name' in users table is a fallback. Let's update it to First Name En + Last Name En
            $newName = ($request->first_name_en ?? $user->individualCustomer->first_name_en) . ' ' . 
                       ($request->last_name_en ?? $user->individualCustomer->last_name_en);
            $user->name = trim($newName) ?: $user->name;
            $user->save();
        }

        $user->refresh();

        return response()->json([
            'status' => true,
            'message' => 'Profile updated successfully',
            'data' => $user->load('individualCustomer')
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
     * Helper: Reverse Geocode (Lat/Lng -> Address & City Name) using OpenStreetMap (Nominatim)
     */
    private function getLocationDataFromCoords($lat, $lng)
    {
        try {
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'User-Agent' => 'MatraqaTec-App/1.0'
            ])->get("https://nominatim.openstreetmap.org/reverse", [
                'format' => 'json',
                'lat' => $lat,
                'lon' => $lng,
                'accept-language' => 'ar', // Prefer Arabic
                'addressdetails' => 1
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $address = $data['address'] ?? [];
                
                // Extract city name (prioritize city, then town, then village, then state)
                $cityName = $address['city'] ?? $address['town'] ?? $address['village'] ?? $address['state'] ?? null;
                
                // Remove common prefixes if needed (usually handled by LIKE match)
                if ($cityName) {
                    $cityName = str_replace(['محافظة ', 'مدينة '], '', $cityName);
                }

                return [
                    'display_name' => $data['display_name'] ?? null,
                    'city_name' => $cityName
                ];
            }
        } catch (\Exception $e) {
            // Log error
        }
        return null;
    }
}
