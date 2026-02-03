<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Order;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    use \App\Traits\HasAutoAssignment;

    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user) return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);

        $query = Appointment::with(['order.service.parent', 'technician.user']);
        
        // 1. Ownership & Security
        if ($user->type === 'technician') {
            $technician = \App\Models\Technician::where('user_id', $user->id)->first();
            if (!$technician) return response()->json(['status' => true, 'message' => 'No appointments', 'data' => []]);
            $query->where('technician_id', $technician->id);
        } elseif ($user->type === 'maintenance_company') {
            $company = $user->maintenanceCompany;
            if (!$company) return response()->json(['status' => true, 'message' => 'No appointments', 'data' => []]);
            $query->whereHas('technician', function($q) use ($company) {
                $q->where('maintenance_company_id', $company->id);
            });
        } else {
            // Customer - Find appointments for their orders
            $query->whereHas('order', function($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }

        // 2. Tab Filter & Status Logic
        // If a specific status is requested, prioritize it over the tab default.
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } elseif ($request->tab === 'previous') {
            $query->whereIn('status', ['completed', 'cancelled']);
        } else {
            // Default to 'current' logic only if no status is specified
            $query->whereIn('status', ['scheduled', 'in_progress']);
        }

        // 3. Search Logic (Order # or Tech Name)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('order', function($o) use ($search) {
                    $o->where('order_number', 'like', "%{$search}%");
                })->orWhereHas('technician.user', function($u) use ($search) {
                    $u->where('name', 'like', "%{$search}%");
                });
            });
        }

        // 4. Advanced Filtering
        // Date Filter
        if ($request->filled('date')) {
            $query->whereDate('appointment_date', $request->date);
        }

        // Precise Hour Filter (e.g., 9, 10, 13)
        if ($request->filled('hour')) {
            $query->whereRaw('HOUR(appointment_date) = ?', [$request->hour]);
        }

        // Time Slot Filter (Legacy support)
        if ($request->filled('time_slot')) {
            switch ($request->time_slot) {
                case 'morning': $query->whereRaw('HOUR(appointment_date) BETWEEN 6 AND 11'); break;
                case 'afternoon': $query->whereRaw('HOUR(appointment_date) BETWEEN 12 AND 16'); break;
                case 'evening': $query->whereRaw('HOUR(appointment_date) BETWEEN 17 AND 23'); break;
            }
        }

        // Category Filter (Parent Service)
        if ($request->filled('category_id')) {
            $query->whereHas('order.service', function($q) use ($request) {
                $q->where('parent_id', $request->category_id);
            });
        }

        // Specific Service Types Filter (Child Services)
        if ($request->filled('service_ids')) {
            $serviceIds = is_array($request->service_ids) ? $request->service_ids : explode(',', $request->service_ids);
            $query->whereHas('order', function($q) use ($serviceIds) {
                $q->whereIn('service_id', $serviceIds);
            });
        }


        
        $appointments = $query->latest('appointment_date')->paginate(15);
        
        return response()->json([
            'status' => true, 
            'message' => 'Appointments retrieved', 
            'data' => $appointments
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'appointment_date' => 'required|date|after:now',
            'technician_id' => 'nullable|exists:technicians,id',
        ]);

        $order = \App\Models\Order::find($request->order_id);
        
        // Ensure user owns the order
        if ($order->user_id !== auth()->id() && auth()->user()->type !== 'admin') {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
        }

        $appointmentDate = \Carbon\Carbon::parse($request->appointment_date);
        $technicianId = $request->technician_id;

        // Auto-assign if no technician provided
        if (!$technicianId) {
            $availableTech = $this->findAvailableTechnician($order->service_id, $order->city_id, $appointmentDate);
            if ($availableTech) {
                $technicianId = $availableTech->id;
            } else {
                $suggestedTime = $this->getSuggestedTime($order->service_id, $order->city_id, $appointmentDate);
                return response()->json([
                    'status' => false,
                    'message' => 'The selected time is currently fully booked.',
                    'suggested_time' => $suggestedTime->toDateTimeString(),
                    'suggestion_message' => 'لا يتوفر فني في الموعد الذي اخترته، ولكن نقترح عليك أقرب موعد متاح.'
                ], 422);
            }
        }

        $appointment = Appointment::create([
            'order_id' => $order->id,
            'technician_id' => $technicianId,
            'appointment_date' => $request->appointment_date,
            'status' => 'scheduled',
        ]);

        // If a technician was assigned, update the order as well and notify
        if ($technicianId) {
            $order->update([
                'technician_id' => $technicianId,
                'status' => 'scheduled',
                'assigned_at' => now(),
            ]);

            $tech = \App\Models\Technician::find($technicianId);
            if ($tech && $tech->user_id) {
                $this->sendNotification($tech->user_id, [
                    'type' => \App\Models\Notification::TYPE_NEW_ORDER ?? 'new_order',
                    'title_ar' => 'موعد جديد',
                    'title_en' => 'New Appointment Assigned',
                    'body_ar' => 'تم تعيين موعد جديد لك، يرجى القبول أو الرفض خلال 15 دقيقة',
                    'body_en' => 'You have been assigned a new appointment. Please accept or reject within 15 minutes',
                    'data' => ['order_id' => $order->id, 'appointment_id' => $appointment->id]
                ]);
            }
        }

        return response()->json(['status' => true, 'message' => 'Appointment scheduled', 'data' => $appointment]);
    }

    public function show($id)
    {
        $user = auth()->user();
        $appointment = Appointment::with(['order.service', 'technician.user'])->find($id);
        
        if (!$appointment) return response()->json(['status' => false, 'message' => 'Not found'], 404);

        // Security check
        if ($user->type !== 'admin' && $appointment->order->user_id !== $user->id) {
            if ($user->type === 'technician') {
                $tech = $user->technician;
                if (!$tech || $appointment->technician_id !== $tech->id) {
                    return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
                }
            } else {
                return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
            }
        }

        return response()->json(['status' => true, 'message' => 'Appointment retrieved', 'data' => $appointment]);
    }

    public function update(Request $request, $id)
    {
        $appointment = Appointment::find($id);
        if (!$appointment) return response()->json(['status' => false, 'message' => 'Not found'], 404);
        
        $user = auth()->user();
        if ($user->type !== 'admin' && $appointment->order->user_id !== $user->id) {
             return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
        }

        $appointment->update($request->all());
        return response()->json(['status' => true, 'message' => 'Appointment updated', 'data' => $appointment]);
    }

    public function destroy($id)
    {
        $appointment = Appointment::find($id);
        if (!$appointment) return response()->json(['status' => false, 'message' => 'Not found'], 404);

        $user = auth()->user();
        if ($user->type !== 'admin' && $appointment->order->user_id !== $user->id) {
             return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
        }

        $appointment->delete();
        return response()->json(['status' => true, 'message' => 'Appointment deleted']);
    }

    private function sendNotification($userId, $details)
    {
        // Simple wrapper for notification logic
        $user = \App\Models\User::find($userId);
        if ($user && $user->fcm_token) {
            // Logic to send FCM would go here
        }
        
        \App\Models\Notification::create([
            'user_id' => $userId,
            'type' => $details['type'],
            'title_ar' => $details['title_ar'],
            'title_en' => $details['title_en'],
            'body_ar' => $details['body_ar'],
            'body_en' => $details['body_en'],
            'data' => $details['data'],
        ]);
    }
}
