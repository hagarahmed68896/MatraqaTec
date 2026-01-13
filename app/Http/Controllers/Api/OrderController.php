<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    use \App\Traits\ValidatesOrderPhotos;

    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user) return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);

        $query = Order::with(['user', 'technician.user', 'service.parent']);

        // 1. Ownership Check
        if ($user->type === 'technician') {
            $technician = \App\Models\Technician::where('user_id', $user->id)->first();
            if (!$technician) return response()->json(['status' => true, 'message' => 'No assigned orders', 'data' => []]);
            $query->where('technician_id', $technician->id);
        } else {
            $query->where('user_id', $user->id);
        }

        // 2. Tab Filter (Current vs Previous)
        if ($request->tab === 'previous') {
            $query->whereIn('status', ['completed', 'cancelled', 'rejected']);
        } else {
            // Default to 'current' if not specified or explicitly 'current'
            $query->whereIn('status', ['new', 'accepted', 'scheduled', 'in_progress']);
        }

        // 3. Advanced Filtering
        // Date Filter
        if ($request->filled('date')) {
            $query->whereDate('scheduled_at', $request->date);
        }

        // Precise Hour Filter
        if ($request->filled('hour')) {
            $query->whereRaw('HOUR(scheduled_at) = ?', [$request->hour]);
        }

        // Time Slot Filter
        if ($request->filled('time_slot')) {
            switch ($request->time_slot) {
                case 'morning': // 6:00 - 11:59
                    $query->whereRaw('HOUR(scheduled_at) BETWEEN 6 AND 11');
                    break;
                case 'afternoon': // 12:00 - 16:59
                    $query->whereRaw('HOUR(scheduled_at) BETWEEN 12 AND 16');
                    break;
                case 'evening': // 17:00 - 23:59
                    $query->whereRaw('HOUR(scheduled_at) BETWEEN 17 AND 23');
                    break;
            }
        }

        // Category Filter (Parent Service)
        if ($request->filled('category_id')) {
            $query->whereHas('service', function($q) use ($request) {
                $q->where('parent_id', $request->category_id);
            });
        }

        // Specific Service Types Filter (Child Services)
        if ($request->filled('service_ids')) {
            $serviceIds = is_array($request->service_ids) ? $request->service_ids : explode(',', $request->service_ids);
            $query->whereIn('service_id', $serviceIds);
        }

        // Specific Status Filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $orders = $query->latest()->paginate(10);
        
        return response()->json([
            'status' => true, 
            'message' => 'Orders retrieved', 
            'data' => $orders
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $request->validate([
            'service_id' => 'required|exists:services,id', // This is the Sub-category ID
            'description' => 'nullable|string',
            'city_id' => 'required|exists:cities,id',
            'address' => 'required|string',
            'payment_method' => 'required|string|in:cash,credit_card,apple_pay,wallet',
            'scheduled_at' => 'required|date|after:now',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|mimes:jpg,jpeg,png,mp4,mov|max:10240',
            'force' => 'nullable|boolean',
        ]);

        // 1. Availability Check Logic
        $scheduledAt = \Carbon\Carbon::parse($request->scheduled_at);
        $isAvailable = $this->checkAvailability($request->city_id, $scheduledAt);

        if (!$isAvailable && !$request->boolean('force')) {
            $suggestedTime = $this->getSuggestedTime($request->city_id, $scheduledAt);
            return response()->json([
                'status' => false,
                'message' => 'The selected time is currently fully booked.',
                'suggested_time' => $suggestedTime->toDateTimeString(),
                'suggestion_message' => 'لا يتوفر فني في الموعد الذي اخترته، ولكن نقترح عليك أقرب موعد متاح.'
            ], 422);
        }

        // 2. Create Order
        $data = $request->except(['attachments', 'force', 'description']);
        $data['order_number'] = 'ORD-' . strtoupper(Str::random(10));
        $data['user_id'] = $user->id;
        $data['notes'] = $request->description;

        // Calculate Pricing based on the specific sub-service
        $service = Service::find($request->service_id);
        $basePrice = $service->price ?: 50; 
        $tax = $basePrice * 0.15;
        $data['total_price'] = $basePrice + $tax;

        // Handle Wallet Payment Case
        if ($request->payment_method === 'wallet') {
            if ($user->wallet_balance < $data['total_price']) {
                return response()->json(['status' => false, 'message' => 'Insufficient wallet balance'], 422);
            }
        }

        $order = Order::create($data);

        // Auto-create Appointment record for the "Appointments" (حجوزاتي) screen
        \App\Models\Appointment::create([
            'order_id' => $order->id,
            'appointment_date' => $order->scheduled_at,
            'status' => 'scheduled',
        ]);

        // 3. Handle Attachments
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('order_attachments', 'public');
                $order->attachments()->create([
                    'file_path' => $path,
                    'type' => Str::contains($file->getMimeType(), 'video') ? 'video' : 'image'
                ]);
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'Order created successfully and sent for admin approval.',
            'data' => [
                'order' => $order->load(['service', 'attachments']),
                'invoice' => [
                    'service_name' => $service->name_ar,
                    'base_price' => $basePrice,
                    'tax' => $tax,
                    'total' => $data['total_price'],
                    'currency' => 'SAR'
                ]
            ]
        ]);
    }

    protected function checkAvailability($cityId, $time)
    {
        // Simple logic: Max 5 orders per city per hour slot
        $start = (clone $time)->startOfHour();
        $end = (clone $time)->endOfHour();
        
        $orderCount = Order::where('city_id', $cityId)
            ->whereBetween('scheduled_at', [$start, $end])
            ->whereIn('status', ['new', 'accepted', 'scheduled', 'in_progress'])
            ->count();

        // Check if city has enough technicians (placeholder logic)
        $techCount = \App\Models\Technician::whereHas('user', function($q) use ($cityId) {
            $q->where('city_id', $cityId);
        })->count() ?: 1;

        return $orderCount < ($techCount * 2); // Assume each tech can handle 2 slot requests
    }

    protected function getSuggestedTime($cityId, $requestedTime)
    {
        // Simply try adding 2 hours until a slot is free
        $suggestion = (clone $requestedTime)->addHours(2);
        while (!$this->checkAvailability($cityId, $suggestion)) {
            $suggestion->addHours(1);
        }
        return $suggestion;
    }

    public function show($id)
    {
        $user = auth()->user();
        $query = Order::with(['user', 'technician', 'service', 'attachments', 'reviews', 'payments', 'appointments'])
                    ->where('id', $id);

        if ($user->type === 'technician') {
            $technician = \App\Models\Technician::where('user_id', $user->id)->first();
            $query->where('technician_id', $technician->id ?? 0);
        } else {
            $query->where('user_id', $user->id);
        }

        $order = $query->first();

        if (!$order) {
            return response()->json(['status' => false, 'message' => 'Order not found'], 404);
        }

        return response()->json(['status' => true, 'message' => 'Order retrieved', 'data' => $order]);
    }

    public function update(Request $request, $id)
    {
        $user = auth()->user();
        $query = Order::where('id', $id);

        if ($user->type === 'technician') {
            $technician = \App\Models\Technician::where('user_id', $user->id)->first();
            $query->where('technician_id', $technician->id ?? 0);
        } else {
            $query->where('user_id', $user->id);
        }

        $order = $query->first();

        if (!$order) {
            return response()->json(['status' => false, 'message' => 'Order not found'], 404);
        }

        $order->update($request->all());

        return response()->json(['status' => true, 'message' => 'Order updated successfully', 'data' => $order]);
    }

    public function startWork(Request $request, $id)
    {
        $order = Order::find($id);
        if (!$order) return response()->json(['status' => false, 'message' => 'Order not found'], 404);

        // Validate photo requirements
        $result = $this->validatePhotoCount($order, 'before');
        if ($result !== true) {
            return response()->json(['status' => false, 'message' => $result], 422);
        }

        $order->update(['status' => 'in_progress']);

        return response()->json(['status' => true, 'message' => 'Work started', 'data' => $order]);
    }

    public function finishWork(Request $request, $id)
    {
        $order = Order::find($id);
        if (!$order) return response()->json(['status' => false, 'message' => 'Order not found'], 404);

        // Security: Ensure the assigned technician is performing this
        $user = auth()->user();
        if ($user->type === 'technician' && $order->technician_id !== $user->technician->id) {
             return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
        }

        // Validate photo requirements
        $result = $this->validatePhotoCount($order, 'after');
        if ($result !== true) {
            return response()->json(['status' => false, 'message' => $result], 422);
        }

        // Validate Spare Parts Input
        $request->validate([
            'spare_parts' => 'nullable|array',
            'spare_parts.*.name' => 'required_with:spare_parts|string',
            'spare_parts.*.qty' => 'required_with:spare_parts|integer|min:1',
            'spare_parts.*.price' => 'required_with:spare_parts|numeric|min:0',
        ]);

        $sparePartsTotal = 0;
        $sparePartsMetadata = [];

        if ($request->has('spare_parts') && is_array($request->spare_parts)) {
            foreach ($request->spare_parts as $part) {
                $lineTotal = $part['qty'] * $part['price'];
                $sparePartsTotal += $lineTotal;
                
                $sparePartsMetadata[] = [
                    'name' => $part['name'],
                    'qty' => $part['qty'],
                    'price' => $part['price'],
                    'total' => $lineTotal
                ];
            }
        }

        // Update Order
        $order->status = 'completed';
        $order->spare_parts_metadata = $sparePartsMetadata;
        // Assuming the current total_price was just the base service price. 
        // If it was 0 or provisional, this logic ensures it sums up correctly.
        $order->total_price = $order->total_price + $sparePartsTotal; 
        
        $order->save();

        return response()->json([
            'status' => true, 
            'message' => 'Work finished', 
            'data' => $order
        ]);
    }

    public function reschedule(Request $request, $id)
    {
        $request->validate([
            'scheduled_at' => 'required|date|after:now',
        ]);

        $order = Order::where('user_id', auth()->id())->find($id);
        if (!$order) return response()->json(['status' => false, 'message' => 'Order not found'], 404);

        if (!in_array($order->status, ['new', 'accepted', 'scheduled'])) {
            return response()->json(['status' => false, 'message' => 'Cannot reschedule an order in this status'], 422);
        }

        $scheduledAt = \Carbon\Carbon::parse($request->scheduled_at);
        if (!$this->checkAvailability($order->city_id, $scheduledAt)) {
            return response()->json(['status' => false, 'message' => 'The selected time is currently fully booked'], 422);
        }

        $order->update(['scheduled_at' => $scheduledAt, 'status' => 'scheduled']);

        return response()->json(['status' => true, 'message' => 'Order rescheduled successfully', 'data' => $order]);
    }

    public function cancel(Request $request, $id)
    {
        $request->validate([
            'cancellation_reason' => 'required|string|max:500',
        ]);

        $order = Order::where('user_id', auth()->id())->find($id);
        if (!$order) return response()->json(['status' => false, 'message' => 'Order not found'], 404);

        if (in_array($order->status, ['completed', 'cancelled', 'rejected'])) {
            return response()->json(['status' => false, 'message' => 'Order is already closed'], 422);
        }

        return \Illuminate\Support\Facades\DB::transaction(function () use ($order, $request) {
            // 1. Mark as cancelled
            $order->update([
                'status' => 'cancelled',
                'rejection_reason' => $request->cancellation_reason,
            ]);

            // 2. Refund logic if paid via wallet or electronic payment
            $paymentCount = $order->payments()->where('status', 'completed')->count();
            if ($paymentCount > 0) {
                $user = $order->user;
                $user->wallet_balance += $order->total_price;
                $user->save();

                \App\Models\WalletTransaction::create([
                    'user_id' => $user->id,
                    'amount' => $order->total_price,
                    'type' => 'refund',
                    'note' => 'Refund for cancelled Order #' . $order->order_number,
                    'reference_id' => $order->id,
                    'reference_type' => Order::class,
                ]);
            }

            return response()->json([
                'status' => true,
                'message' => 'Order cancelled and amount refunded to wallet',
                'data' => $order
            ]);
        });
    }

    public function destroy($id)
    {
        // Users usually shouldn't hard delete orders, but providing 'cancel' logic in update or destroy.
        // Allowing destroy if ownership matches.
        $order = Order::where('user_id', auth()->id())->where('id', $id)->first();
        if (!$order) return response()->json(['status' => false, 'message' => 'Not found'], 404);
        $order->delete();
        return response()->json(['status' => true, 'message' => 'Order deleted']);
    }
}
