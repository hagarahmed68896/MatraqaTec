<?php

namespace App\Http\Controllers\Api;
 
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
 
class UserSettingController extends Controller
{
    /**
     * Get user settings
     */
    public function index()
    {
        $user = auth()->user();
        return response()->json([
            'status' => true,
            'message' => 'Settings retrieved successfully',
            'data' => [
                'notification_enabled' => $user->notification_enabled,
                'night_mode' => $user->night_mode,
                'language' => $user->language,
            ]
        ]);
    }
 
    /**
     * Update user settings
     */
    public function update(Request $request)
    {
        $user = auth()->user();
 
        $validator = Validator::make($request->all(), [
            'notification_enabled' => 'sometimes|boolean',
            'night_mode' => 'sometimes|boolean',
            'language' => 'sometimes|string|in:ar,en',
        ]);
 
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }
 
        $user->update($request->only([
            'notification_enabled',
            'night_mode',
            'language'
        ]));
 
        return response()->json([
            'status' => true,
            'message' => 'Settings updated successfully',
            'data' => [
                'notification_enabled' => $user->notification_enabled,
                'night_mode' => $user->night_mode,
                'language' => $user->language,
            ]
        ]);
    }
}
