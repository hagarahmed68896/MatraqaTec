<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TechnicianLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TrackingController extends Controller
{
    /**
     * Update technician's current location.
     */
    public function updateLocation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }

        $user = $request->user();
        
        if ($user->type !== 'technician') {
            return response()->json(['status' => false, 'message' => 'Only technicians can update location'], 403);
        }

        $technician = \App\Models\Technician::where('user_id', $user->id)->first();
        if (!$technician) {
            return response()->json(['status' => false, 'message' => 'Technician profile not found'], 404);
        }

        $location = TechnicianLocation::updateOrCreate(
            ['technician_id' => $technician->id],
            [
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
            ]
        );

        return response()->json([
            'status' => true,
            'message' => 'Location updated successfully',
            'data' => $location,
        ]);
    }

    /**
     * Get technician's current location (for clients).
     */
    public function getLocation($technician_id)
    {
        $location = TechnicianLocation::where('technician_id', $technician_id)->first();

        if (!$location) {
            return response()->json(['status' => false, 'message' => 'Location not available'], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Location retrieved successfully',
            'data' => $location,
        ]);
    }
}
