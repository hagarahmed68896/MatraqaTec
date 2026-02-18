<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    public function index(Request $request)
    {
        // 1. Handle Week Range
        $startDate = $request->filled('start_date') 
            ? \Carbon\Carbon::parse($request->start_date) 
            : \Carbon\Carbon::now()->startOfWeek(\Carbon\CarbonInterface::SUNDAY);
        
        $endDate = $startDate->copy()->endOfWeek(\Carbon\CarbonInterface::SATURDAY);

        $query = Appointment::with([
            'order.service', 
            'order.user',
            'technician.user',
            'order.reviews'
        ])->whereBetween('appointment_date', [$startDate, $endDate]);

        // 2. Filter by status
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // 3. Search Logic
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('technician.user', function($q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%");
                })->orWhereHas('order', function($q3) use ($search) {
                    $q3->where('order_number', 'like', "%{$search}%");
                });
            });
        }

        $items = $query->get();

        // 4. Stats (Filtered by the current week view or global? Let's do global for overview as per standard)
        $stats = [
            'all' => Appointment::whereBetween('appointment_date', [$startDate, $endDate])->count(),
            'scheduled' => Appointment::whereBetween('appointment_date', [$startDate, $endDate])->where('status', 'scheduled')->count(),
            'in_progress' => Appointment::whereBetween('appointment_date', [$startDate, $endDate])->where('status', 'in_progress')->count(),
            'completed' => Appointment::whereBetween('appointment_date', [$startDate, $endDate])->where('status', 'completed')->count(),
        ];

        return view('admin.appointments.index', compact('items', 'stats', 'startDate', 'endDate'));
    }

    public function show($id)
    {
        $item = Appointment::with(['order', 'technician'])->findOrFail($id);
        return view('admin.appointments.show', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $appointment = Appointment::findOrFail($id);
        $appointment->update($request->all());
        return back()->with('success', __('Appointment updated successfully.'));
    }

    public function destroy($id)
    {
        $appointment = Appointment::findOrFail($id);
        $appointment->delete();
        return redirect()->route('admin.appointments.index')->with('success', __('Appointment deleted successfully.'));
    }
}
