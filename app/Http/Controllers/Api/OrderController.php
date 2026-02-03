<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    use \App\Traits\ValidatesOrderPhotos, \App\Traits\HasAutoAssignment;

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
        } elseif ($user->type === 'maintenance_company') {
            $company = \App\Models\MaintenanceCompany::where('user_id', $user->id)->first();
            if (!$company) return response()->json(['status' => false, 'message' => 'Company profile not found'], 404);
            $query->where('maintenance_company_id', $company->id);
        } else {
            $query->where('user_id', $user->id);
        }


        // 2. Tab Filter
        if ($user->type === 'maintenance_company') {
            // Company-specific tabs
            if ($request->tab === 'all') {
                // All orders: assigned to company OR available new orders in their city/service
                $query->where(function($q) use ($company) {
                    $q->where('maintenance_company_id', $company->id)
                      ->orWhere(function($q2) use ($company) {
                          $q2->where('status', 'new')
                             ->where('city_id', $company->city_id)
                             ->whereHas('service', function($q3) use ($company) {
                                 $q3->whereIn('id', $company->services->pluck('id'));
                             });
                      });
                });
            } elseif ($request->tab === 'new') {
                // New orders only: status = 'new' in company's city and matching services
                $query->where('status', 'new')
                      ->where('city_id', $company->city_id)
                      ->whereHas('service', function($q) use ($company) {
                          $q->whereIn('id', $company->services->pluck('id'));
                      });
            } elseif ($request->tab === 'in_progress') {
                // In Progress: accepted, scheduled, in_progress (assigned to this company)
                $query->where('maintenance_company_id', $company->id)
                      ->whereIn('status', ['accepted', 'scheduled', 'in_progress']);
            } elseif ($request->tab === 'assigned') {
                 // Orders assigned to technicians but not yet accepted by them (if we want to track this separately)
                 // or maybe 'pending_technician_acceptance'
                 $query->where('maintenance_company_id', $company->id)
                       ->whereNotNull('technician_id')
                       ->where('status', 'scheduled'); // Assuming 'scheduled' is the status when assigned
            } else {
                // Default: show all assigned orders
                $query->where('maintenance_company_id', $company->id);
            }
        } else {
            // Client/Technician tabs (existing logic)
            if ($request->tab === 'under_review') {
                // قيد المراجعة - Pending admin approval
                $query->where('status', 'new');
            } elseif ($request->tab === 'previous') {
                // السابقة - Completed/Cancelled/Rejected
                $query->whereIn('status', ['completed', 'cancelled', 'rejected']);
            } else {
                // Default to 'current' - الحالية
                // في الطريق, وصل, مجدولة, بدأ العمل
                // Maps to: accepted, scheduled, in_progress (and sub_status variations)
                $query->whereIn('status', ['accepted', 'scheduled', 'in_progress']);
            }
        }


        // 3. Advanced Filtering
        // Single Date Filter
        if ($request->filled('date')) {
            $query->whereDate('scheduled_at', $request->date);
        }

        // Date Range Filter
        if ($request->filled('date_from')) {
            $query->whereDate('scheduled_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('scheduled_at', '<=', $request->date_to);
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
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'service_id' => 'required|exists:services,id',
            'description' => 'nullable|string',
            'city_id' => 'required|exists:cities,id',
            'address' => 'required|string',
            'payment_method' => 'required|string|in:cash,credit_card,apple_pay,wallet',
            'scheduled_at' => 'required|date|after:now',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|mimes:jpg,jpeg,png,mp4,mov|max:10240',
            'force' => 'nullable|boolean',
        ], [
            'scheduled_at.after' => 'The scheduled date must be a future date.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        // 1. Availability Check Logic
        $scheduledAt = \Carbon\Carbon::parse($request->scheduled_at);
        $availableTech = $this->findAvailableTechnician($request->service_id, $request->city_id, $scheduledAt);

        if (!$availableTech && !$request->boolean('force')) {
            $suggestedTime = $this->getSuggestedTime($request->service_id, $request->city_id, $scheduledAt);
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

        if ($availableTech) {
            $data['technician_id'] = $availableTech->id;
            $data['status'] = 'scheduled';
            $data['assigned_at'] = now();
        }

        $order = Order::create($data);

        // Auto-create Appointment record for the "Appointments" (حجوزاتي) screen
        \App\Models\Appointment::create([
            'order_id' => $order->id,
            'technician_id' => $order->technician_id,
            'appointment_date' => $order->scheduled_at,
            'status' => 'scheduled',
        ]);

        // Trigger Notification for Tech if assigned, else Admin/Company
        if ($availableTech) {
            $this->sendNotification($availableTech->user_id, [
                'type' => \App\Models\Notification::TYPE_NEW_ORDER ?? 'new_order',
                'title_ar' => 'مهمة جديدة',
                'title_en' => 'New  Task',
                'body_ar' => 'تم تعيين مهمة جديدة لك، يرجى القبول أو الرفض خلال 15 دقيقة',
                'body_en' => 'A new task has been automatically assigned to you. Please accept or reject within 15 minutes',
                'data' => ['order_id' => $order->id]
            ]);
        } else {
            $this->notifyNewOrder($order);
        }

        // 3. Handle Attachments
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('order_attachments', 'public');
                $order->attachments()->create([
                    'file_path' => $path,
                    'type' => 'before'
                ]);
            }
        }

        return response()->json([
            'status' => true,
            'message' => $availableTech ? 'Order created successfully and scheduled.' : 'Order created successfully and sent for admin approval.',
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

    // Availability, findAvailableTechnician, and getSuggestedTime are now handled by HasAutoAssignment trait

    public function show($id)
    {
        $user = auth()->user();
        $query = Order::with(['user', 'technician', 'service.parent', 'attachments', 'reviews', 'payments', 'appointments'])
                    ->where('id', $id);

        if ($user->type === 'technician') {
            $technician = \App\Models\Technician::where('user_id', $user->id)->first();
            $query->where('technician_id', $technician->id ?? 0);
        } elseif ($user->type === 'maintenance_company') {
            $company = \App\Models\MaintenanceCompany::where('user_id', $user->id)->first();
            $query->where('maintenance_company_id', $company->id ?? 0);
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
        } elseif ($user->type === 'maintenance_company') {
            $company = \App\Models\MaintenanceCompany::where('user_id', $user->id)->first();
            $query->where('maintenance_company_id', $company->id ?? 0);
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

        // Security: Ensure assigned tech
        $user = auth()->user();
        if ($user->type === 'technician' && $order->technician_id !== $user->technician->id) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
        }

        // Handle Attachments if provided in the request
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('order_attachments', 'public');
                $order->attachments()->create([
                    'file_path' => $path,
                    'type' => 'before'
                ]);
            }
        }

        // Note: Photo validation moved to workStarted (actual start of work) step.

        $order->update([
            'status' => 'in_progress',
            'sub_status' => 'on_way'
        ]);

        $this->sendNotification($order->user_id, [
            'type' => \App\Models\Notification::TYPE_WORK_STARTED,
            'title_ar' => 'الفني في الطريق',
            'title_en' => 'Technician on the way',
            'body_ar' => 'الفني بدأ العمل وهو حالياً في الطريق إليك',
            'body_en' => 'The technician has started work and is on the way to you',
            'data' => ['order_id' => $order->id]
        ]);

        return response()->json(['status' => true, 'message' => 'Work started - Technician is on the way', 'data' => $order]);
    }

    /**
     * Technician arrived at location (وصل)
     */
    public function arrived(Request $request, $id)
    {
        $request->merge(['sub_status' => 'arrived']);
        return $this->updateSubStatus($request, $id);
    }

    /**
     * Technician started actual work (بدأ العمل)
     */
    public function workStarted(Request $request, $id)
    {
        $request->merge(['sub_status' => 'work_started']);
        return $this->updateSubStatus($request, $id);
    }

    public function updateSubStatus(Request $request, $id)
    {
        $order = Order::find($id);
        if (!$order) return response()->json(['status' => false, 'message' => 'Order not found'], 404);

        $user = auth()->user();
        if ($user->type === 'technician' && $order->technician_id !== $user->technician->id) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
        }

        if ($order->status !== 'in_progress') {
            return response()->json(['status' => false, 'message' => 'Order must be in progress to update sub-status'], 422);
        }

        // Handle Attachments if provided
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('order_attachments', 'public');
                $order->attachments()->create([
                    'file_path' => $path,
                    'type' => 'before' // Typically "before" covers everything up to work_started
                ]);
            }
        }

        $request->validate([
            'sub_status' => 'required|string|in:on_way,arrived,work_started,additional_visit'
        ]);

        $order->update(['sub_status' => $request->sub_status]);

        // Validation: If work started, check for 'before' photos
        if ($request->sub_status === 'work_started') {
            $result = $this->validatePhotoCount($order, 'before');
            if ($result !== true) {
                // Rollback sub_status if photos missing (optional, but safer)
                $order->update(['sub_status' => 'arrived']); 
                return response()->json(['status' => false, 'message' => $result], 422);
            }
        }

        // Trigger relevant notifications based on sub-status
        $notificationData = [
            'arrived' => [
                'ar' => ['title' => 'وصل الفني', 'body' => 'لقد وصل الفني إلى موقعك الآن'],
                'en' => ['title' => 'Technician Arrived', 'body' => 'The technician has arrived at your location']
            ],
            'work_started' => [
                'ar' => ['title' => 'بدأ العمل', 'body' => 'بدأ الفني العمل على طلبك الآن'],
                'en' => ['title' => 'Work Started', 'body' => 'The technician has started working on your request']
            ],
        ];

        if (isset($notificationData[$request->sub_status])) {
            $this->sendNotification($order->user_id, [
                'type' => \App\Models\Notification::TYPE_STATUS_UPDATE,
                'title_ar' => $notificationData[$request->sub_status]['ar']['title'],
                'title_en' => $notificationData[$request->sub_status]['en']['title'],
                'body_ar' => $notificationData[$request->sub_status]['ar']['body'],
                'body_en' => $notificationData[$request->sub_status]['en']['body'],
                'data' => ['order_id' => $order->id, 'sub_status' => $request->sub_status]
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Status updated successfully',
            'data' => $order
        ]);
    }

    /**
     * Update spare parts list without finishing the order (Add/Increment/Decrement)
     */
    public function updateSpareParts(Request $request, $id)
    {
        $order = Order::find($id);
        if (!$order) return response()->json(['status' => false, 'message' => 'Order not found'], 404);

        $user = auth()->user();
        if ($user->type === 'technician' && $order->technician_id !== $user->technician->id) {
             return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'spare_parts' => 'required|array',
            'spare_parts.*.name' => 'required|string',
            'spare_parts.*.qty' => 'required|integer|min:0',
            'spare_parts.*.price' => 'required|numeric|min:0',
        ]);

        $sparePartsMetadata = [];
        foreach ($request->spare_parts as $part) {
            if ($part['qty'] > 0) {
                $sparePartsMetadata[] = [
                    'name' => $part['name'],
                    'qty' => $part['qty'],
                    'price' => $part['price'],
                    'total' => $part['qty'] * $part['price']
                ];
            }
        }

        $order->update(['spare_parts_metadata' => $sparePartsMetadata]);

        return response()->json([
            'status' => true,
            'message' => 'Spare parts updated successfully',
            'data' => $order
        ]);
    }

    /**
     * Request an additional visit (طلب زيارة إضافية)
     */
    public function requestAdditionalVisit(Request $request, $id)
    {
        $order = Order::find($id);
        if (!$order) return response()->json(['status' => false, 'message' => 'Order not found'], 404);

        $user = auth()->user();
        if ($user->type === 'technician' && $order->technician_id !== $user->technician->id) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'note' => 'nullable|string'
        ]);

        $order->update([
            'sub_status' => 'additional_visit',
            'notes' => $request->note ? "Additional Visit Reason: " . $request->note : $order->notes
        ]);

        $bodyAr = 'الفني يحتاج لزيارة إضافية لاستكمال العمل على طلبك رقم ' . $order->order_number;
        $bodyEn = 'The technician needs an additional visit to complete work on your order #' . $order->order_number;

        if ($request->note) {
            $bodyAr .= "\nالسبب: " . $request->note;
            $bodyEn .= "\nReason: " . $request->note;
        }

        $this->sendNotification($order->user_id, [
            'type' => \App\Models\Notification::TYPE_STATUS_UPDATE,
            'title_ar' => 'طلب زيارة إضافية',
            'title_en' => 'Additional Visit Requested',
            'body_ar' => $bodyAr,
            'body_en' => $bodyEn,
            'data' => ['order_id' => $order->id, 'note' => $request->note]
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Additional visit request sent successfully',
            'data' => $order
        ]);
    }

    /**
     * Send Invoice to Client (إرسال للعميل)
     */
    public function sendInvoiceToClient(Request $request, $id)
    {
        $order = Order::with('service')->find($id);
        if (!$order) return response()->json(['status' => false, 'message' => 'Order not found'], 404);

        $user = auth()->user();
        if ($user->type === 'technician' && $order->technician_id !== $user->technician->id) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
        }

        // Calculate current total
        $basePrice = $order->service->price ?? 50;
        $tax = $basePrice * 0.15;
        $sparePartsTotal = 0;
        
        if ($order->spare_parts_metadata) {
            foreach ($order->spare_parts_metadata as $part) {
                $sparePartsTotal += ($part['qty'] * $part['price']);
            }
        }

        $grandTotal = $basePrice + $tax + $sparePartsTotal;

        // Optionally update total_price in DB now so client sees it
        $order->update(['total_price' => $grandTotal]);

        $this->sendNotification($order->user_id, [
            'type' => \App\Models\Notification::TYPE_STATUS_UPDATE,
            'title_ar' => 'متطلبات المهمة والتكاليف',
            'title_en' => 'Mission Requirements & Billing',
            'body_ar' => "تم إرسال تفاصيل الفاتورة لطلبك رقم {$order->order_number}. الإجمالي: {$grandTotal} ريال",
            'body_en' => "Invoice details sent for your order #{$order->order_number}. Total: {$grandTotal} SAR",
            'data' => [
                'order_id' => $order->id, 
                'total' => $grandTotal,
                'spare_parts' => $order->spare_parts_metadata
            ]
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Invoice sent to client successfully',
            'data' => [
                'total_price' => $grandTotal,
                'spare_parts' => $order->spare_parts_metadata
            ]
        ]);
    }

    /**
     * Save Completion Photos before signature (متابعة للتوقيع)
     */
    public function saveCompletionPhotos(Request $request, $id)
    {
        $order = Order::find($id);
        if (!$order) return response()->json(['status' => false, 'message' => 'Order not found'], 404);

        $user = auth()->user();
        if ($user->type === 'technician' && $order->technician_id !== $user->technician->id) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
        }

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('order_attachments', 'public');
                $order->attachments()->create([
                    'file_path' => $path,
                    'type' => 'after'
                ]);
            }
        }

        $result = $this->validatePhotoCount($order, 'after');
        if ($result !== true) {
            return response()->json(['status' => false, 'message' => $result], 422);
        }

        return response()->json([
            'status' => true,
            'message' => 'Photos uploaded successfully. Proceed to signature.',
            'data' => $order->load('attachments')
        ]);
    }

    public function finishWork(Request $request, $id)
    {
        $order = Order::find($id);
        if (!$order) return response()->json(['status' => false, 'message' => 'Order not found'], 404);

        $user = auth()->user();
        if ($user->type === 'technician' && $order->technician_id !== $user->technician->id) {
             return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
        }

        // Handle Attachments (After Photos)
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('order_attachments', 'public');
                $order->attachments()->create([
                    'file_path' => $path,
                    'type' => 'after'
                ]);
            }
        }

        // Validate photo requirements (if mandatory 'after' photos)
        $result = $this->validatePhotoCount($order, 'after');
        if ($result !== true) {
            return response()->json(['status' => false, 'message' => $result], 422);
        }

        $request->validate([
            'spare_parts' => 'nullable|array',
            'spare_parts.*.name' => 'required_with:spare_parts|string',
            'spare_parts.*.qty' => 'required_with:spare_parts|integer|min:1',
            'spare_parts.*.price' => 'required_with:spare_parts|numeric|min:0',
            'client_signature' => 'nullable|string', // Base64 signature
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
        $order->sub_status = null;
        $order->spare_parts_metadata = $sparePartsMetadata;
        $order->total_price = $order->total_price + $sparePartsTotal; 
        
        if ($request->filled('client_signature')) {
            $order->client_signature = $request->client_signature;
        }
        
        $order->save();

        $this->sendNotification($order->user_id, [
            'type' => \App\Models\Notification::TYPE_WORK_FINISHED,
            'title_ar' => 'تم الانتهاء من العمل',
            'title_en' => 'Work Completed',
            'body_ar' => 'لقد قام الفني بإنهاء العمل بنجاح. يمكنك الآن مراجعة الفاتورة',
            'body_en' => 'The technician has successfully finished the work. You can now review the invoice',
            'data' => ['order_id' => $order->id]
        ]);

        return response()->json([
            'status' => true, 
            'message' => 'Work finished successfully', 
            'data' => $order
        ]);
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
        $query = Order::where('id', $id);
        if ($user->type === 'maintenance_company') {
            $company = \App\Models\MaintenanceCompany::where('user_id', $user->id)->first();
            $query->where('maintenance_company_id', $company->id ?? 0);
        } else {
            $query->where('user_id', $user->id);
        }
        $order = $query->first();
        if (!$order) return response()->json(['status' => false, 'message' => 'Not found'], 404);
        $order->delete();
        return response()->json(['status' => true, 'message' => 'Order deleted']);
    }

    public function resend(Request $request, $id)
    {
        $oldOrder = Order::where('id', $id)->where('user_id', $request->user()->id)->first();
        if (!$oldOrder) return response()->json(['status' => false, 'message' => 'Order not found'], 404);

        if (!in_array($oldOrder->status, ['cancelled', 'completed', 'rejected'])) {
            return response()->json(['status' => false, 'message' => 'Can only resend cancelled/completed orders'], 422);
        }

        $newOrder = Order::create([
            'order_number' => 'ORD-' . strtoupper(Str::random(10)),
            'user_id' => $oldOrder->user_id,
            'city_id' => $oldOrder->city_id,
            'service_id' => $oldOrder->service_id,
            'status' => 'new',
            'total_price' => $oldOrder->total_price,
            'payment_method' => $oldOrder->payment_method,
            'scheduled_at' => $request->scheduled_at ?? now()->addDay(),
            'address' => $oldOrder->address,
            'notes' => $oldOrder->notes,
        ]);

        return response()->json(['status' => true, 'message' => 'Order resent', 'data' => $newOrder]);
    }

    public function reschedule(Request $request, $id)
    {
        $user = auth()->user();
        $order = Order::where('id', $id)->where('user_id', $user->id)->first();

        if (!$order) {
            return response()->json(['status' => false, 'message' => 'Order not found'], 404);
        }

        if (in_array($order->status, ['completed', 'cancelled', 'rejected'])) {
            return response()->json(['status' => false, 'message' => 'Closed orders cannot be rescheduled'], 422);
        }

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'scheduled_at' => 'required|date|after:now',
        ], [
            'scheduled_at.after' => 'The scheduled date must be a future date.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        $order->update([
            'scheduled_at' => $request->scheduled_at,
        ]);

        // Update related Appointment records
        \App\Models\Appointment::where('order_id', $order->id)->update([
            'appointment_date' => $request->scheduled_at,
        ]);

        // Notify technician if assigned
        if ($order->technician_id) {
            $this->sendNotification($order->technician->user_id, [
                'type' => \App\Models\Notification::TYPE_ORDER_RESCHEDULED ?? 'order_rescheduled',
                'title_ar' => 'تم تعديل موعد الطلب',
                'title_en' => 'Order Rescheduled',
                'body_ar' => "قام العميل بتعديل موعد الطلب رقم {$order->order_number} إلى {$order->scheduled_at}",
                'body_en' => "The client has rescheduled order #{$order->order_number} to {$order->scheduled_at}",
                'data' => ['order_id' => $order->id]
            ]);
        }

        return response()->json(['status' => true, 'message' => 'Order rescheduled successfully', 'data' => $order]);
    }

    public function getInvoice($id)
    {
        $order = Order::with(['service', 'user'])->find($id);
        if (!$order) return response()->json(['status' => false, 'message' => 'Order not found'], 404);

        $base = $order->total_price / 1.15;
        $tax = $order->total_price - $base;

        return response()->json([
            'status' => true,
            'data' => [
                'invoice_number' => 'INV-' . $order->order_number,
                'date' => $order->created_at->format('Y-m-d'),
                'client' => $order->user->name,
                'service' => $order->service->name_ar,
                'base_amount' => number_format($base, 2),
                'tax' => number_format($tax, 2),
                'total' => number_format($order->total_price, 2),
                'spare_parts' => $order->spare_parts_metadata ?? [],
            ]
        ]);
    }

    public function getTechnicianLocation($id)
    {
        $order = Order::with('technician')->find($id);
        if (!$order || !$order->technician_id) {
            return response()->json(['status' => false, 'message' => 'Technician not assigned'], 404);
        }

        $loc = \App\Models\TechnicianLocation::where('technician_id', $order->technician_id)->first();
        if (!$loc) return response()->json(['status' => false, 'message' => 'Location unavailable'], 404);

        return response()->json([
            'status' => true,
            'data' => [
                'name' => $order->technician->user->name ?? 'Unknown',
                'latitude' => $loc->latitude,
                'longitude' => $loc->longitude,
                'updated' => $loc->updated_at->diffForHumans(),
            ]
        ]);
    }
    public function accept(Request $request, $id)
    {
        $user = auth()->user();
        $technician = null;
        $company = null;

        if ($user->type === 'maintenance_company') {
            $company = \App\Models\MaintenanceCompany::where('user_id', $user->id)->first();
            if (!$company) return response()->json(['status' => false, 'message' => 'Company profile not found'], 404);
        } elseif ($user->type === 'technician') {
            $technician = \App\Models\Technician::where('user_id', $user->id)->first();
            if (!$technician) return response()->json(['status' => false, 'message' => 'Technician profile not found'], 404);
            // If part of a company, the company admin should handle acceptance usually, 
            // but we can allow it if the system design allows tech-level acceptance.
        } else {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
        }

        // Allow accepting if:
        // 1. It's already assigned to this company/tech
        // 2. OR it's a NEW order in the same city and hasn't been assigned yet
        $orderQuery = Order::where('id', $id);
        
        if ($company) {
            $orderQuery->where(function($q) use ($company) {
                $q->where('maintenance_company_id', $company->id)
                  ->orWhere(function($q2) use ($company) {
                      $q2->whereNull('maintenance_company_id')
                         ->where('status', 'new')
                         ->where('city_id', $company->city_id);
                  });
            });
        } else {
            $orderQuery->where(function($q) use ($technician) {
                $q->where('technician_id', $technician->id)
                  ->orWhere(function($q2) use ($technician) {
                      $q2->whereNull('technician_id')
                         ->where('status', 'new')
                         ->where('city_id', $technician->user->city_id);
                  });
            });
        }

        $order = $orderQuery->first();

        if (!$order) {
            return response()->json(['status' => false, 'message' => 'Order not found or already assigned'], 404);
        }

        if ($order->status !== 'new' && $order->maintenance_company_id !== ($company->id ?? null) && $order->technician_id !== ($technician->id ?? null)) {
            return response()->json(['status' => false, 'message' => 'Order already processed'], 422);
        }

        $request->validate([
            'technician_id' => 'nullable|exists:technicians,id',
            'scheduled_at' => 'nullable|date',
        ]);

        $order->status = 'scheduled';
        if ($company) {
            $order->maintenance_company_id = $company->id;
        }
        if ($technician) {
            $order->technician_id = $technician->id;
        }

        if ($request->technician_id && $company) {
            $order->technician_id = $request->technician_id;
            $order->assigned_at = now(); // Start the timer for the technician

            // Notify Technician
            $tech = \App\Models\Technician::find($request->technician_id);
            if ($tech && $tech->user_id) {
                 $this->sendNotification($tech->user_id, [
                    'type' => \App\Models\Notification::TYPE_NEW_ORDER ?? 'new_order',
                    'title_ar' => 'مهمة جديدة',
                    'title_en' => 'New Task Assigned',
                    'body_ar' => 'تم تعيين مهمة جديدة لك، يرجى القبول أو الرفض خلال 15 دقيقة',
                    'body_en' => 'You have been assigned a new task. Please accept or reject within 15 minutes',
                    'data' => ['order_id' => $order->id]
                ]);
            }
        }
        if ($request->scheduled_at) {
            $order->scheduled_at = $request->scheduled_at;
        }
        
        $order->save();

        $this->sendNotification($order->user_id, [
            'type' => \App\Models\Notification::TYPE_ORDER_ACCEPTED,
            'title_ar' => 'تم قبول طلبك',
            'title_en' => 'Your order was accepted',
            'body_ar' => 'قامت شركة الصيانة بقبول طلبك وتحديد موعد للزيارة',
            'body_en' => 'The maintenance company has accepted your order and scheduled a visit',
            'data' => ['order_id' => $order->id]
        ]);

        return response()->json(['status' => true, 'message' => 'Order accepted', 'data' => $order]);
    }

    public function technicianAccept(Request $request, $id)
    {
        $user = $request->user();
        if ($user->type !== 'technician') {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
        }

        $technician = \App\Models\Technician::where('user_id', $user->id)->first();
        if (!$technician) return response()->json(['status' => false, 'message' => 'Technician profile not found'], 404);

        $order = Order::find($id);
        if (!$order) {
            return response()->json(['status' => false, 'message' => 'Order not found'], 404);
        }

        if ($order->technician_id !== $technician->id) {
            return response()->json(['status' => false, 'message' => 'Order is not assigned to you'], 403);
        }

        // 15-minute expiration check
        if ($order->assigned_at && $order->assigned_at->diffInMinutes(now()) > 15) {
            return response()->json(['status' => false, 'message' => 'Order request expired (15 minutes limit)'], 422);
        }

        if ($order->status !== 'scheduled') { // Assuming 'scheduled' is the state when assigned by company/admin
             // Also handling 'new' if self-picking logic exists, but primary flow is Company -> Tech
             if ($order->status === 'accepted' || $order->status === 'in_progress') {
                 return response()->json(['status' => false, 'message' => 'Order already accepted'], 422);
             }
        }

        $order->status = 'accepted'; // Or keep it 'scheduled' but mark as 'tech_accepted'? 
        // For now, let's look at the Order status flow. 
        // If Company assigns -> Status is 'scheduled'?
        // The previous accept() method sets status to 'scheduled'.
        // So here we might want to move it to 'accepted' or just confirm it. 
        // Let's set it to 'accepted' which maps to 'مقبول' (Accepted) in getStatusLabelAttribute
        $order->status = 'accepted'; 
        $order->save();

        // Notify client or company?
        // Notify Company that tech accepted?
        if ($order->maintenance_company_id) {
            $companyUser = \App\Models\MaintenanceCompany::find($order->maintenance_company_id)->user_id ?? null;
            if ($companyUser) {
                 $this->sendNotification($companyUser, [
                    'type' => \App\Models\Notification::TYPE_STATUS_UPDATE,
                    'title_ar' => 'الفني قبل المهمة',
                    'title_en' => 'Technician Accepted Task',
                    'body_ar' => "قام الفني {$technician->name_ar} بقبول المهمة رقم {$order->order_number}",
                    'body_en' => "Technician {$technician->name_en} accepted task #{$order->order_number}",
                    'data' => ['order_id' => $order->id]
                ]);
            }
        }

        return response()->json(['status' => true, 'message' => 'Order accepted successfully', 'data' => $order]);
    }

    public function technicianRefuse(Request $request, $id)
    {
        $user = $request->user();
        if ($user->type !== 'technician') {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
        }

        $technician = \App\Models\Technician::where('user_id', $user->id)->first();
        if (!$technician) return response()->json(['status' => false, 'message' => 'Technician profile not found'], 404);

        $order = Order::where('id', $id)->where('technician_id', $technician->id)->first();

        if (!$order) {
            return response()->json(['status' => false, 'message' => 'Order not found'], 404);
        }

        // 15-minute expiration check (Still relevant? If expired, can they refuse? Yes, to clear it.)
        // But maybe we don't block refusal if expired.

        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $order->technician_id = null; // Unassign
        $order->assigned_at = null;   // Clear assignment time
        $order->status = 'new';       // Back to new? Or keep 'scheduled' but unassigned?
        // If it was assigned by company, it should go back to company pool or remain assigned to company but no tech.
        // Assuming status 'scheduled' meant confirmed visit. 
        // If tech rejects, maybe we should notify company to re-assign.
        $order->status = 'new'; // Let's reset to new or specific 'pending' status.
        // If it belongs to a company, it stays with company.
        
        // If the order was originally just 'new' and picked up, this is fine.
        // If it was 'scheduled' by admin, we might need a distinct status.
        // For simplicity, reset to 'new' but keep company ownership if exists.
        
        $order->save();

        // Notify Company
        if ($order->maintenance_company_id) {
            $companyUser = \App\Models\MaintenanceCompany::find($order->maintenance_company_id)->user_id ?? null;
            if ($companyUser) {
                 $this->sendNotification($companyUser, [
                    'type' => \App\Models\Notification::TYPE_ORDER_REJECTED,
                    'title_ar' => 'الفني رفض المهمة',
                    'title_en' => 'Technician Rejected Task',
                    'body_ar' => "قام الفني {$technician->name_ar} برفض المهمة رقم {$order->order_number}. السبب: {$request->rejection_reason}",
                    'body_en' => "Technician {$technician->name_en} rejected task #{$order->order_number}. Reason: {$request->rejection_reason}",
                    'data' => ['order_id' => $order->id]
                ]);
            }
        }

        return response()->json(['status' => true, 'message' => 'Order refused', 'data' => $order]);
    }

    public function refuse(Request $request, $id)
    {
        $user = auth()->user();
        $order = null;

        if ($user->type === 'maintenance_company') {
            $company = \App\Models\MaintenanceCompany::where('user_id', $user->id)->first();
            $order = Order::where('id', $id)->where('maintenance_company_id', $company->id ?? 0)->first();
        } elseif ($user->type === 'technician') {
            $technician = \App\Models\Technician::where('user_id', $user->id)->first();
            $order = Order::where('id', $id)->where('technician_id', $technician->id ?? 0)->first();
        }

        if (!$order) {
            return response()->json(['status' => false, 'message' => 'Order not found'], 404);
        }

        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $order->status = 'rejected';
        $order->rejection_reason = $request->rejection_reason;
        $order->save();

        // Notify client
        $this->sendNotification($order->user_id, [
            'type' => \App\Models\Notification::TYPE_ORDER_REJECTED,
            'title_ar' => 'تم رفض طلبك',
            'title_en' => 'Your order was rejected',
            'body_ar' => 'نعتذر، تم رفض طلبك للسبب التالي: ' . $order->rejection_reason,
            'body_en' => 'Sorry, your order was rejected for: ' . $order->rejection_reason,
            'data' => ['order_id' => $order->id]
        ]);

        return response()->json(['status' => true, 'message' => 'Order refused', 'data' => $order]);
    }

    private function notifyNewOrder($order)
    {
        // For companies that might provide this service
        $companies = \App\Models\MaintenanceCompany::whereHas('services', function($q) use ($order) {
            $q->where('services.id', $order->service_id);
        })->where('city_id', $order->city_id)->get();

        foreach ($companies as $company) {
            $this->sendNotification($company->user_id, [
                'type' => \App\Models\Notification::TYPE_NEW_ORDER,
                'title_ar' => 'طلب صيانة جديد',
                'title_en' => 'New Maintenance Request',
                'body_ar' => 'يوجد طلب جديد لخدمة ' . ($order->service->name_ar ?? ''),
                'body_en' => 'New request for ' . ($order->service->name_en ?? '') . ' service',
                'data' => ['order_id' => $order->id]
            ]);
        }
    }

    private function sendNotification($userId, $details)
    {
        $user = \App\Models\User::find($userId);
        
        if ($user && $user->notification_enabled) {
            \App\Models\Notification::create([
                'user_id' => $userId,
                'type' => $details['type'],
                'title_ar' => $details['title_ar'],
                'title_en' => $details['title_en'],
                'body_ar' => $details['body_ar'],
                'body_en' => $details['body_en'],
                'data' => $details['data'] ?? [],
                'status' => 'sent',
                'is_read' => false
            ]);
        }
    }
}
