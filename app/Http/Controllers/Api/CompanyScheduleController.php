<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CompanySchedule;
use Illuminate\Http\Request;

class CompanyScheduleController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        if ($user->type !== 'maintenance_company') {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
        }

        $company = $user->maintenanceCompany;
        if (!$company) {
            return response()->json(['status' => false, 'message' => 'Company profile not found'], 404);
        }

        $schedules = CompanySchedule::where('maintenance_company_id', $company->id)
            ->orderByRaw("FIELD(day, 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday')")
            ->get()
            ->groupBy('day');

        return response()->json([
            'status' => true,
            'message' => 'Schedules retrieved successfully',
            'data' => $schedules
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        if ($user->type !== 'maintenance_company') {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
        }

        $company = $user->maintenanceCompany;
        if (!$company) {
            return response()->json(['status' => false, 'message' => 'Company profile not found'], 404);
        }

        $validated = $request->validate([
            'day' => 'required|string|in:Sunday,Monday,Tuesday,Wednesday,Thursday,Friday,Saturday',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);

        $schedule = CompanySchedule::create([
            'maintenance_company_id' => $company->id,
            'day' => $validated['day'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Schedule added successfully',
            'data' => $schedule
        ]);
    }

    public function update(Request $request, $id)
    {
        $user = $request->user();
        $schedule = CompanySchedule::find($id);

        if (!$schedule) {
            return response()->json(['status' => false, 'message' => 'Schedule not found'], 404);
        }

        if ($user->type !== 'maintenance_company' || $schedule->maintenance_company_id !== $user->maintenanceCompany->id) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'day' => 'sometimes|string|in:Sunday,Monday,Tuesday,Wednesday,Thursday,Friday,Saturday',
            'start_time' => 'sometimes|date_format:H:i',
            'end_time' => 'sometimes|date_format:H:i|after:start_time',
        ]);

        $schedule->update($validated);

        return response()->json([
            'status' => true,
            'message' => 'Schedule updated successfully',
            'data' => $schedule
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        $schedule = CompanySchedule::find($id);

        if (!$schedule) {
            return response()->json(['status' => false, 'message' => 'Schedule not found'], 404);
        }

        if ($user->type !== 'maintenance_company' || $schedule->maintenance_company_id !== $user->maintenanceCompany->id) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
        }

        $schedule->delete();

        return response()->json([
            'status' => true,
            'message' => 'Schedule deleted successfully'
        ]);
    }
}
