<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserSettingController extends Controller
{
    /**
     * Get Current User Settings
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        return response()->json([
            'status' => true,
            'message' => 'User settings retrieved',
            'data' => [
                'notification_enabled' => (bool) $user->notification_enabled,
                'night_mode' => (bool) $user->night_mode,
                'language' => $user->language ?? 'ar',
            ]
        ]);
    }

    /**
     * Update User Settings
     */
    public function update(Request $request)
    {
        $user = $request->user();
        
        $validator = Validator::make($request->all(), [
            'notification_enabled' => 'nullable|boolean',
            'night_mode'           => 'nullable|boolean',
            'language'             => 'nullable|string|in:ar,en',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        if ($request->has('notification_enabled')) {
            $user->notification_enabled = $request->notification_enabled;
        }

        if ($request->has('night_mode')) {
            $user->night_mode = $request->night_mode;
        }

        if ($request->has('language')) {
            $user->language = $request->language;
        }

        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'Settings updated successfully',
            'data' => [
                'notification_enabled' => (bool) $user->notification_enabled,
                'night_mode' => (bool) $user->night_mode,
                'language' => $user->language,
            ]
        ]);
    }
}
