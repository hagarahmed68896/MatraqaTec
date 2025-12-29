<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Appointment::with(['order', 'technician']);
        if ($request->has('technician_id')) {
            $query->where('technician_id', $request->technician_id);
        }
        $appointments = $query->orderBy('created_at', 'desc')->paginate(15);
        return response()->json(['status' => true, 'message' => 'Appointments retrieved', 'data' => $appointments]);
    }

    public function show($id)
    {
        $appointment = Appointment::with(['order', 'technician'])->find($id);
        if (!$appointment) return response()->json(['status' => false, 'message' => 'Not found'], 404);
        return response()->json(['status' => true, 'message' => 'Appointment retrieved', 'data' => $appointment]);
    }

    public function update(Request $request, $id)
    {
        $appointment = Appointment::find($id);
        if (!$appointment) return response()->json(['status' => false, 'message' => 'Not found'], 404);
        $appointment->update($request->all());
        return response()->json(['status' => true, 'message' => 'Appointment updated', 'data' => $appointment]);
    }

    public function destroy($id)
    {
        $appointment = Appointment::find($id);
        if (!$appointment) return response()->json(['status' => false, 'message' => 'Not found'], 404);
        $appointment->delete();
        return response()->json(['status' => true, 'message' => 'Appointment deleted']);
    }
}
