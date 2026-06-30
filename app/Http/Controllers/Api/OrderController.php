<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    use \App\Traits\ValidatesOrderPhotos, \App\Traits\HasAutoAssignment, \App\Traits\HandlesLocation, \App\Traits\HandlesRejectionPenalty;

    public function index(Request $request)
    {
        \App\Models\Order::syncExpiredOrders();

        $user = $request->user();
        if (!$user) return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);

        // Auto-detach expired unaccepted orders for all technicians before retrieving the list
        $duration = (int) \App\Models\Setting::getByKey('order_acceptance_duration', 15);
        
        // 1. Company-assigned orders whose technician timed out: remove technician, keep 'scheduled' under company
        \App\Models\Order::whereNotNull('technician_id')
            ->whereNotNull('maintenance_company_id')
            ->where('sub_status', 'pending_tech_acceptance')
            ->where('assigned_at', '<', now()->subMinutes($duration))
            ->update([
                'technician_id' => null,
                'assigned_at' => null,
                'sub_status' => null,
                // Keep status as 'scheduled' — company still owns it, just needs to re-assign
            ]);

        // 2. Independent (marketplace) orders whose technician timed out: reset fully to 'new'
        \App\Models\Order::whereNotNull('technician_id')
            ->whereNull('maintenance_company_id')
            ->where(function($q) {
                $q->where('status', 'scheduled')
                  ->orWhere('status', 'new');
            })
            ->where('assigned_at', '<', now()->subMinutes($duration))
            ->update([
                'technician_id' => null,
                'status' => 'new',
                'assigned_at' => null,
                'sub_status' => null,
            ]);

        $query = Order::with(['user', 'technician' => function($q) {
            $q->withAvg('reviews', 'rating')->withCount('reviews');
        }, 'technician.user', 'service.parent']);

        // 1. Ownership Check
        if ($user->type === 'technician') {
            $technician = \App\Models\Technician::where('user_id', $user->id)->first();
            if (!$technician) return response()->json(['status' => true, 'message' => 'No assigned orders', 'data' => []]);
            
            $query->where(function($q) use ($technician, $request, $user) {
                // 1. Orders directly assigned to this technician
                $q->where('technician_id', $technician->id);
                
                // 2. Marketplace orders (new and unassigned) - Only if independent tech and looking at 'new' tab
                if (!$technician->maintenance_company_id && ($request->tab === 'new' || $request->tab === 'under_review')) {
                    $q->orWhere(function($sq) use ($user, $technician) {
                        $sq->where('status', 'new')
                           ->whereNull('technician_id')
                           ->where('city_id', $user->city_id)
                           ->where(function($q_rej) use ($user) {
                               $q_rej->whereNull('rejected_by_user_id')
                                     ->orWhere('rejected_by_user_id', '!=', $user->id);
                           })
                           ->where(function($sq2) use ($technician) {
                               $sq2->where('service_id', $technician->service_id)
                                   ->orWhereHas('service', function($q2) use ($technician) {
                                       $q2->where('parent_id', $technician->category_id);
                                   });
                           });
                    });
                }
            });

            // If technician belongs to a company, hide pure 'new' orders (waiting for company to accept first)
            // unless they were specifically assigned to him.
            if ($technician->maintenance_company_id) {
                $query->where(function($q) {
                    $q->where('status', '!=', 'new')
                      ->orWhereNotNull('technician_id'); // If tech_id is set, it's for him
                });
            }
        } elseif ($user->type === 'maintenance_company') {
            $company = \App\Models\MaintenanceCompany::where('user_id', $user->id)->first();
            if (!$company) return response()->json(['status' => false, 'message' => 'Company profile not found'], 404);
            $query->where('maintenance_company_id', $company->id);
        } else {
            $query->where('user_id', $user->id);
        }


        // 2. Tab Filter (Apply only if NOT searching by date, time, or keyword)
        if (!$request->filled('search') && !$request->filled('date') && !$request->filled('time') && !$request->filled('time_slot')) {
            if ($user->type === 'maintenance_company') {
            // Company-specific tabs
            if ($request->tab === 'all') {
                // All CURRENT orders: (assigned to company OR marketplace) AND status is active
                $query->whereIn('status', ['new', 'accepted', 'scheduled', 'in_progress'])
                      ->where(function($q) use ($company) {
                          $q->where('maintenance_company_id', $company->id)
                            ->orWhere(function($q2) use ($company) {
                                $q2->whereNull('maintenance_company_id')
                                   ->where('city_id', $company->city_id)
                                   ->whereHas('service', function($q3) use ($company) {
                                       $q3->whereIn('id', $company->services->pluck('id'));
                                   });
                            });
                      });
            } elseif ($request->tab === 'new') {
                // New orders: status = 'new' AND (Marketplace OR specifically assigned to this company)
                $query->where('status', 'new')
                      ->where(function($q) use ($company) {
                          $q->where('maintenance_company_id', $company->id)
                            ->orWhere(function($q2) use ($company) {
                                $q2->whereNull('maintenance_company_id')
                                   ->where('city_id', $company->city_id)
                                   ->whereHas('service', function($q3) use ($company) {
                                       $q3->whereIn('id', $company->services->pluck('id'));
                                   });
                            });
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
            } elseif ($request->tab === 'previous') {
                // Previous: completed, cancelled, rejected (assigned to this company)
                $query->where('maintenance_company_id', $company->id)
                      ->whereIn('status', ['completed', 'cancelled', 'rejected']);
            } else {
                // Default: show all assigned orders
                $query->where('maintenance_company_id', $company->id);
            }
        } else {
            // Client/Technician tabs (existing logic)
            if ($request->tab === 'under_review' || $request->tab === 'new') {
                // تاب "جديد" للفني:
                // 1. الطلبات الجديدة المستقلة (status=new, بدون شركة)
                // 2. الطلبات المُعيَّن له من شركة وفي انتظار قبوله (pending_tech_acceptance)
                // 3. طلبات إعادة الجدولة المعلقة
                // 4. طلبات التعيين التلقائي للفني المستقل (status=scheduled)
                $query->where(function($q) use ($user) {
                    $q->where('sub_status', 'pending_tech_acceptance') // ← جديد: معيّن من شركة
                      ->orWhere('sub_status', 'reschedule_requested')  // طلب إعادة جدولة
                      ->orWhere(function($sub) use ($user) {
                          $sub->where('status', 'new');
                          if ($user->type === 'technician') {
                              $sub->whereNull('maintenance_company_id'); // independent only
                          }
                      })
                      ->orWhere(function($sub) {
                          $sub->where('status', 'scheduled')
                              ->whereNull('sub_status'); // Auto-assigned independent
                      });
                });
            } elseif ($request->tab === 'previous') {
                // السابقة - Completed/Cancelled/Rejected
                $query->whereIn('status', ['completed', 'cancelled', 'rejected']);
            } else {
                // Default to 'current' - الحالية (المجدول والمقبول وقيد التنفيذ)
                // يُستثنى منها pending_tech_acceptance عشان ما يظهرش في المجدول
                if (!$request->filled('status')) {
                    $query->whereIn('status', ['accepted', 'scheduled', 'in_progress'])
                          ->where(function($q) {
                              $q->whereNull('sub_status')
                                ->orWhereNotIn('sub_status', ['reschedule_requested', 'pending_tech_acceptance']);
                          });
                }
            }
            }
        }


        // 3. Search Logic (Technician searches Client, Client searches Technician)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search, $user) {
                $q->where('order_number', 'like', "%{$search}%");
                
                // Add Date Search Support (YYYY-MM-DD)
                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $search)) {
                    $q->orWhereDate('scheduled_at', $search);
                }
                
                // Add Time Search Support (HH:mm)
                if (preg_match('/^\d{2}:\d{2}$/', $search)) {
                    $q->orWhereRaw('TIME_FORMAT(scheduled_at, "%H:%i") = ?', [$search]);
                }
                
                if ($user->type === 'technician') {
                    $q->orWhereHas('user', function($u) use ($search) {
                        $u->where('name', 'like', "%{$search}%")
                          ->orWhere('phone', 'like', "%{$search}%");
                    });
                } else {
                    $q->orWhereHas('technician.user', function($u) use ($search) {
                        $u->where('name', 'like', "%{$search}%")
                          ->orWhere('phone', 'like', "%{$search}%");
                    });
                }
            });
        }

        // 4. Advanced Filtering (Matching the Mobile UI Filter)
        
        // A. Date Filter (Calendar)
        if ($request->filled('date')) {
            $date = $request->date;
            if ($date === 'today') $date = now()->toDateString();
            elseif ($date === 'tomorrow') $date = now()->addDay()->toDateString();
            $query->whereDate('scheduled_at', $date);
        }

        // B. Time Filter (Suitable Time/Time Slots)
        if ($request->filled('time')) {
            // Support single time or array of times
            $times = is_array($request->time) ? $request->time : explode(',', $request->time);
            $query->where(function($q) use ($times) {
                foreach ($times as $t) {
                    $q->orWhereRaw('TIME_FORMAT(scheduled_at, "%H:%i") = ?', [$t]);
                }
            });
        }

        if ($request->filled('time_slot')) {
            switch ($request->time_slot) {
                case 'morning': $query->whereRaw('HOUR(scheduled_at) BETWEEN 6 AND 11'); break;
                case 'afternoon': $query->whereRaw('HOUR(scheduled_at) BETWEEN 12 AND 16'); break;
                case 'evening': $query->whereRaw('HOUR(scheduled_at) BETWEEN 17 AND 23'); break;
            }
        }

        // C. Service Type Filter (Multi-select)
        if ($request->filled('service_ids')) {
            $serviceIds = is_array($request->service_ids) ? $request->service_ids : explode(',', $request->service_ids);
            $query->whereIn('service_id', $serviceIds);
        }

        // D. Status Filter (Matching UI Radio Buttons)
        if ($request->filled('status')) {
            $status = $request->status;
            // Handle sub-statuses for the 'Status' filter
            if ($status === 'on_way') {
                $query->where('status', 'in_progress')->where('sub_status', 'on_way');
            } elseif ($status === 'arrived') {
                $query->where('status', 'in_progress')->where('sub_status', 'arrived');
            } elseif ($status === 'work_started') {
                $query->where('status', 'in_progress')->where('sub_status', 'work_started');
            } elseif ($status === 'scheduled') {
                $query->where('status', 'scheduled')
                      ->where(function($q) {
                          $q->whereNull('sub_status')
                            ->orWhere('sub_status', '!=', 'reschedule_requested');
                      });
            } else {
                $query->where('status', $status);
            }
        }

        // E. Category Filter (الفئة - Parent Service)
        if ($request->filled('category_id')) {
            $query->whereHas('service', function($q) use ($request) {
                $q->where('parent_id', $request->category_id)
                  ->orWhere('id', $request->category_id);
            });
        }

        // F. Payment Method Filter (الدفعة)
        if ($request->filled('payment_method')) {
            $methods = is_array($request->payment_method)
                ? $request->payment_method
                : explode(',', $request->payment_method);
            $query->whereIn('payment_method', $methods);
        }

        $orders = $query->latest()->paginate(10);
        
        $orders->getCollection()->transform(function ($order) use ($user) {
            if ($user->type === 'technician' && $order->sub_status === 'pending_tech_acceptance') {
                $order->status = 'new';
            }

            if ($order->technician) {
                $order->technician_data = $this->formatTechnicianData($order->technician);
                // Keeping original technician for backward compatibility if needed, 
                // but adding the customized object as well.
                // Or we can just replace the technician object. 
                // Let's replace it to match the user's specific request for the technician object.
                $order->setRelation('technician', $order->technician_data);
            }
            return $order;
        });

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
            'sub_service_id' => 'nullable|exists:services,id',
            'description' => 'nullable|string',
            'city_id' => 'nullable|exists:cities,id',
            'address' => 'required|string',
            'payment_method' => 'nullable|string|in:cash,credit_card,apple_pay,wallet',
            'scheduled_at' => 'required|date|after:now',
            'attachments' => 'nullable',
            'attachments.*' => 'file|mimes:jpg,jpeg,png,mp4,mov|max:10240',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'force' => 'nullable|boolean',
        ], [
            'scheduled_at.after' => 'يجب أن يكون تاريخ الموعد في المستقبل.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        // Detect City from Logic if not provided
        $cityId = $request->city_id ?? $this->detectCityFromCoords($request->latitude, $request->longitude);
        
        // Fallback to user's registered city if detection fails
        if (!$cityId && $user) {
            $cityId = $user->city_id;
        }

        // 1. Availability Check Logic
        $scheduledAt = \Carbon\Carbon::parse($request->scheduled_at);
        
        // Priority to sub_service_id if provided
        $targetServiceId = $request->sub_service_id ?? $request->service_id;
        $availableTech = $this->findAvailableTechnician($targetServiceId, $cityId, $scheduledAt, $request->latitude, $request->longitude);

        if (!$availableTech && !$request->boolean('force')) {
            $suggestedTime = $this->getSuggestedTime($targetServiceId, $cityId, $scheduledAt, $request->latitude, $request->longitude);
            
            $debugDiagnostics = config('app.debug') 
                ? $this->getDiagnostics($targetServiceId, $cityId, $scheduledAt, $request->latitude, $request->longitude) 
                : null;

            // If suggestedTime is null, it means there are NO technicians for this service at all
            if (!$suggestedTime) {
                return response()->json(array_filter([
                    'status' => false,
                    'message' => 'No technician available for this service in your area currently.',
                    'suggestion_message' => 'عذراً، لا يوجد فني متاح لهذه الخدمة في منطقتك حالياً، سنعمل على توفيرها قريباً.',
                    'debug_diagnostics' => $debugDiagnostics
                ]), 200);
            }

            return response()->json(array_filter([
                'status' => false,
                'message' => 'The selected time is currently fully booked.',
                'suggested_time' => $suggestedTime->toDateTimeString(),
                'suggestion_message' => 'لا يتوفر فني في الموعد الذي اخترته، ولكن نقترح عليك أقرب موعد متاح.',
                'debug_diagnostics' => $debugDiagnostics
            ]), 200);
        }

        // 2. Create Order
        $data = $request->except(['attachments', 'force', 'description', 'sub_service_id']);
        $data['city_id'] = $cityId;
        $data['city_id'] = $cityId;
        // Generate Sequential Order Number (using ID + 1 or a timestamp based logic for simplicity)
        // Since we can't know the exact ID before insertion without locking, we can use a timestamp 
        // or just accept ID after creation. Let's use a simple numeric strategy:
        // However, retrieving max(id) is prone to race conditions.
        // Better approach: We'll set a temporary value, create, then update to ID.
        // OR: use a separate sequence. For this request, let's use max('id') + 1 for now as a simple sequential number.
        $lastOrder = Order::latest('id')->first();
        $nextId = $lastOrder ? $lastOrder->id + 1 : 1;
        $data['order_number'] = $nextId;
        $data['user_id'] = $user->id;
        $data['notes'] = $request->description;
        $data['latitude'] = $request->latitude;
        $data['longitude'] = $request->longitude;

        // Calculate Pricing based on selected services
        $basePrice = 0;
        if ($request->filled('sub_service_id')) {
            $subService = Service::find($request->sub_service_id);
            $basePrice = $subService->price ?: 50;
        } else {
            $service = Service::find($request->service_id);
            $basePrice = $service->price ?: 50; 
        }

        $tax = $basePrice * 0.15;
        $data['total_price'] = $basePrice + $tax;

        // Initial Payment is now delayed until the end (Base Price + Spare Parts).
        // Therefore, we do not deduct or check wallet balance here.

        if ($availableTech) {
            $data['technician_id'] = $availableTech->id;
            $data['maintenance_company_id'] = $availableTech->maintenance_company_id;
            // If technician belongs to a company, order stays 'new' so company can review/accept.
            // If independent technician, it becomes 'scheduled' immediately.
            $data['status'] = $availableTech->maintenance_company_id ? 'new' : 'scheduled';
            $data['assigned_at'] = now();
        }

        $order = Order::create($data);

        // Sync Services to order_services
        $servicesToSync = [$request->service_id];
        if ($request->filled('sub_service_id')) {
            $servicesToSync[] = $request->sub_service_id;
        }
        $order->services()->sync(array_unique($servicesToSync));

        // Auto-create Appointment record for the "Appointments" (حجوزاتي) screen
        \App\Models\Appointment::create([
            'order_id' => $order->id,
            'technician_id' => $order->technician_id,
            'appointment_date' => $order->scheduled_at,
            'status' => 'scheduled',
        ]);

        // Trigger Notification for Tech if assigned, else Admin/Company
        if ($availableTech) {
            $notificationTargetId = $availableTech->user_id;
            
            // If it belongs to a company, notify the company user instead of the tech directly (or in addition to)
            // Based on user request, the company should handle "New" orders.
            if ($availableTech->maintenance_company_id) {
                $company = \App\Models\MaintenanceCompany::find($availableTech->maintenance_company_id);
                if ($company && $company->user_id) {
                    $notificationTargetId = $company->user_id;
                }
            }

            $this->sendNotification($notificationTargetId, [
                'type' => \App\Models\Notification::TYPE_NEW_ORDER ?? 'new_order',
                'title_ar' => 'مهمة جديدة',
                'title_en' => 'New Task',
                'body_ar' => 'تم استلام طلب جديد، يرجى مراجعته وقبوله',
                'body_en' => 'A new task has been received. Please review and accept it.',
                'data' => ['order_id' => $order->id]
            ]);
        } else {
            $this->notifyNewOrder($order);
        }

        // 3. Handle Attachments
        if ($request->hasFile('attachments')) {
            $files = $request->file('attachments');
            if (is_array($files)) {
                foreach ($files as $file) {
                    $fileName = time() . '_' . $file->getClientOriginalName();
                    $file->move(public_path('uploads/order_attachments'), $fileName);
                    $path = 'uploads/order_attachments/' . $fileName;
                    $order->attachments()->create([
                        'file_path' => $path,
                        'type' => 'before'
                    ]);
                }
            } else {
                // Handle single file upload
                $fileName = time() . '_' . $files->getClientOriginalName();
                $files->move(public_path('uploads/order_attachments'), $fileName);
                $path = 'uploads/order_attachments/' . $fileName;
                $order->attachments()->create([
                    'file_path' => $path,
                    'type' => 'before'
                ]);
            }
            // Refresh to load attachments relationships
            $order->refresh();
        }

        return response()->json([
            'status' => true,
            'message' => __('Order created successfully and scheduled.'),
            'data' => [
                'order' => $order,
                'invoice' => [
                    'base_price' => number_format($basePrice, 2),
                    'tax' => number_format($tax, 2),
                    'total' => number_format($data['total_price'], 2),
                    'currency' => 'SAR'
                ]
            ]
        ]);
    }

    // Availability, findAvailableTechnician, and getSuggestedTime are now handled by HasAutoAssignment trait

    public function show($id)
    {
        $user = auth()->user();
        $query = Order::with(['user', 'technician' => function($q) {
                        $q->with(['maintenanceCompany', 'category'])->withAvg('reviews', 'rating')->withCount('reviews');
                    }, 'service.parent', 'attachments', 'reviews', 'payments', 'appointments'])
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

        if ($order->technician) {
            $order->technician_data = $this->formatTechnicianData($order->technician);
            $order->setRelation('technician', $order->technician_data);
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

        // Reset timer if technician is changed/set
        if ($request->has('technician_id') && ($request->technician_id != $order->technician_id)) {
            $order->assigned_at = $request->technician_id ? now() : null;
        }

        $order->update($request->all());

        return response()->json(['status' => true, 'message' => 'Order updated successfully', 'data' => $order]);
    }

    public function startWork(Request $request, $id)
    {
        $order = $this->getOrderIfAuthorized($id);
        if (!$order) return response()->json(['status' => false, 'message' => __('Order not found')], 404);

        // Validation: Prevent starting if scheduled_at is more than 24 hours in the past
        if ($this->isOrderExpired($order)) {
            return response()->json([
                'status' => false, 
                'message' => __('The order cannot be started because the scheduled time is too old (more than 24 hours). Please reschedule the order first.'),
                'data' => $order
            ], 422);
        }

        // Handle Attachments if provided in the request
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $this->uploadAttachment($file);
                $order->attachments()->create([
                    'file_path' => $path,
                    'type' => 'before'
                ]);
            }

            // Notify about Inspection photos
            $this->sendNotification($order->user_id, [
                'type' => \App\Models\Notification::TYPE_ORDER_PHOTOS,
                'title_ar' => 'تم إضافة صور المعاينة',
                'title_en' => 'Inspection photos added',
                'body_ar' => 'قام الفني بإضافة صور المعاينة (قبل البدء) لطلبك رقم ' . $order->order_number,
                'body_en' => 'The technician added inspection photos (before start) to your order #' . $order->order_number,
                'data' => ['order_id' => $order->id, 'photo_type' => 'before']
            ]);
        }

        // Note: Photo validation moved to workStarted (actual start of work) step.

        $order->update([
            'status' => 'in_progress',
            'sub_status' => 'on_way'
        ]);

        // Mark technician as busy
        if ($order->technician) {
            $order->technician->update(['availability_status' => 'busy']);
        }

        // Update previous notifications to remove old action buttons
        $this->updateNotificationType($order->user_id, $order->id, \App\Models\Notification::TYPE_TECHNICIAN_ON_WAY, [
            \App\Models\Notification::TYPE_ORDER_ACCEPTED,
            \App\Models\Notification::TYPE_ORDER_RESCHEDULED
        ]);

        $this->sendNotification($order->user_id, [
            'type' => \App\Models\Notification::TYPE_TECHNICIAN_ON_WAY,
            'title_ar' => 'الفني في الطريق',
            'title_en' => 'Technician on the way',
            'body_ar' => 'الفني بدأ المهمة وهو حالياً في الطريق إليك',
            'body_en' => 'The technician has started the task and is on the way to you',
            'data' => ['order_id' => $order->id]
        ]);

        return response()->json(['status' => true, 'message' => __('Work started - Technician is on the way'), 'data' => $order]);
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
        $order = $this->getOrderIfAuthorized($id);
        if (!$order) return response()->json(['status' => false, 'message' => __('Order not found')], 404);

        if ($order->status !== 'in_progress') {
            return response()->json(['status' => false, 'message' => 'Order must be in progress to update sub-status'], 422);
        }

        // Validation: Prevent updating if scheduled_at is more than 24 hours in the past
        if ($this->isOrderExpired($order)) {
            return response()->json([
                'status' => false, 
                'message' => __('This order status cannot be updated because its time is too old (more than 24 hours). Please reschedule the order first.'),
                'data' => $order
            ], 422);
        }

        // Handle Attachments if provided
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $this->uploadAttachment($file);
                $order->attachments()->create([
                    'file_path' => $path,
                    'type' => 'before' // Typically "before" covers everything up to work_started
                ]);
            }

            // Notify about new photos
            $this->sendNotification($order->user_id, [
                'type' => \App\Models\Notification::TYPE_ORDER_PHOTOS,
                'title_ar' => 'تم إضافة صور جديدة',
                'title_en' => 'New photos added',
                'body_ar' => 'قام الفني بإضافة صور جديدة لمتابعة حالة طلبك رقم ' . $order->order_number,
                'body_en' => 'The technician added new photos to follow up on your order #' . $order->order_number,
                'data' => ['order_id' => $order->id, 'photo_type' => 'before']
            ]);
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
            $type = ($request->sub_status === 'arrived') 
                    ? \App\Models\Notification::TYPE_TECHNICIAN_ARRIVED 
                    : \App\Models\Notification::TYPE_WORK_STARTED;

            $this->sendNotification($order->user_id, [
                'type' => $type,
                'title_ar' => $notificationData[$request->sub_status]['ar']['title'],
                'title_en' => $notificationData[$request->sub_status]['en']['title'],
                'body_ar' => $notificationData[$request->sub_status]['ar']['body'],
                'body_en' => $notificationData[$request->sub_status]['en']['body'],
                'data' => ['order_id' => $order->id, 'sub_status' => $request->sub_status]
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => __('Status updated successfully'),
            'data' => $order
        ]);
    }

    /**
     * Update spare parts list without finishing the order (Add/Increment/Decrement)
     */
    public function updateSpareParts(Request $request, $id)
    {
        $order = $this->getOrderIfAuthorized($id);
        if (!$order) return response()->json(['status' => false, 'message' => __('Order not found')], 404);

        $request->validate([
            'spare_parts' => 'required|array',
            'spare_parts.*.name' => 'required|string',
            'spare_parts.*.qty' => 'required|integer|min:0',
            'spare_parts.*.price' => 'required|numeric|min:0',
        ]);

        $sparePartsTotal = 0;
        $sparePartsMetadata = [];
        foreach ($request->spare_parts as $part) {
            if ($part['qty'] > 0) {
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

        // Calculate Grand Total (Service + Parts + Tax) using separate rates
        $basePrice = $order->service->price ?? 50;
        $serviceTaxRate = (float) \App\Models\Setting::getByKey('vat_rate_services', 15) / 100;
        $partsTaxRate = (float) \App\Models\Setting::getByKey('vat_rate_parts', 15) / 100;

        $serviceTax = $basePrice * $serviceTaxRate;
        $partsTax = $sparePartsTotal * $partsTaxRate;

        $subTotal = $basePrice + $sparePartsTotal;
        $tax = $serviceTax + $partsTax;
        $grandTotal = $subTotal + $tax;

        $order->update([
            'spare_parts_metadata' => $sparePartsMetadata,
            'total_price' => $grandTotal
        ]);

        // Trigger Notification for Client to review spare parts invoice
        $this->sendNotification($order->user_id, [
            'type' => 'spare_parts_added',
            'title_ar' => 'تم إضافة متطلبات وتحديث الفاتورة',
            'title_en' => 'Requirements added and invoice updated',
            'body_ar' => 'قام الفني بإضافة متطلبات وقطع غيار جديدة لطلبك. يرجى مراجعة الفاتورة.',
            'body_en' => 'The technician added new requirements and spare parts to your order. Please review the invoice.',
            'data' => ['order_id' => $order->id]
        ]);

        return response()->json([
            'status' => true,
            'message' => __('Spare parts updated successfully'),
            'data' => $order
        ]);
    }

    /**
     * Request an additional visit (طلب زيارة إضافية)
     */
    public function requestAdditionalVisit(Request $request, $id)
    {
        $order = $this->getOrderIfAuthorized($id);
        if (!$order) return response()->json(['status' => false, 'message' => __('Order not found')], 404);

        $request->validate([
            'note' => 'nullable|string',
            'next_visit_time' => 'required|date|after:now',
        ]);

        $order->update([
            'status' => 'scheduled', // Return to scheduled pool
            'sub_status' => 'additional_visit',
            'proposed_scheduled_at' => $request->next_visit_time,
            'reschedule_requested_by' => 'technician',
            'notes' => $request->note ? "Additional Visit Reason: " . $request->note : $order->notes
        ]);

        // Release technician availability
        if ($order->technician) {
            $order->technician->update(['availability_status' => 'available']);
        }

        $formattedTime = \Carbon\Carbon::parse($request->next_visit_time)->format('Y-m-d H:i');
        $bodyAr = 'الفني يحتاج لزيارة إضافية لاستكمال العمل على طلبك رقم ' . $order->order_number . ". الموعد المقترح: $formattedTime";
        $bodyEn = 'The technician needs an additional visit to complete work on your order #' . $order->order_number . ". Proposed time: $formattedTime";

        if ($request->note) {
            $bodyAr .= "\nالسبب: " . $request->note;
            $bodyEn .= "\nReason: " . $request->note;
        }

        $this->sendNotification($order->user_id, [
            'type' => \App\Models\Notification::TYPE_ORDER_RESCHEDULED ?? 'order_rescheduled',
            'title_ar' => 'طلب زيارة إضافية وموعد جديد',
            'title_en' => 'Additional Visit & New Appointment Requested',
            'body_ar' => $bodyAr,
            'body_en' => $bodyEn,
            'data' => ['order_id' => $order->id, 'note' => $request->note, 'proposed_time' => $request->next_visit_time]
        ]);

        return response()->json([
            'status' => true,
            'message' => __('Additional visit request sent successfully'),
            'data' => $order
        ]);
    }

    /**
     * Send Invoice to Client (إرسال للعميل)
     */
    public function sendInvoiceToClient(Request $request, $id)
    {
        $order = $this->getOrderIfAuthorized($id);
        if (!$order) return response()->json(['status' => false, 'message' => __('Order not found')], 404);

        // Calculate current total
        $basePrice = $order->service->price ?? 50;
        $sparePartsTotal = 0;
        if ($order->spare_parts_metadata) {
            foreach ($order->spare_parts_metadata as $part) {
                $sparePartsTotal += ($part['qty'] * $part['price']);
            }
        }

        $taxRate = (float) \App\Models\Setting::getByKey('vat_rate', 15) / 100;
        $subTotal = $basePrice + $sparePartsTotal;
        $tax = $subTotal * $taxRate;
        $grandTotal = $subTotal + $tax;

        // Update total_price in DB now so client sees it
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
            'message' => __('Invoice sent to client successfully'),
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
        $order = $this->getOrderIfAuthorized($id);
        if (!$order) return response()->json(['status' => false, 'message' => __('Order not found')], 404);

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $this->uploadAttachment($file);
                $order->attachments()->create([
                    'file_path' => $path,
                    'type' => 'after'
                ]);
            }

            // Notify about Completion photos
            $this->sendNotification($order->user_id, [
                'type' => \App\Models\Notification::TYPE_ORDER_PHOTOS,
                'title_ar' => 'تم إضافة صور إتمام العمل',
                'title_en' => 'Completion photos added',
                'body_ar' => 'قام الفني بإضافة صور إتمام العمل (بعد الإنتهاء) لطلبك رقم ' . $order->order_number,
                'body_en' => 'The technician added completion photos (after finish) to your order #' . $order->order_number,
                'data' => ['order_id' => $order->id, 'photo_type' => 'after']
            ]);
        }

        $result = $this->validatePhotoCount($order, 'after');
        if ($result !== true) {
            return response()->json(['status' => false, 'message' => $result], 422);
        }

        return response()->json([
            'status' => true,
            'message' => __('Photos uploaded successfully. Proceed to signature.'),
            'data' => $order->load('attachments')
        ]);
    }

    public function finishWork(Request $request, $id)
    {
        $order = $this->getOrderIfAuthorized($id);
        if (!$order) return response()->json(['status' => false, 'message' => __('Order not found')], 404);

        // Restriction: Ensure the invoice is paid before finishing
        if (!$order->is_paid) {
            return response()->json([
                'status' => false, 
                'message' => __('Please ensure the invoice is paid before finishing the work.')
            ], 422);
        }

        // Handle Attachments (After Photos)
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $this->uploadAttachment($file);
                $order->attachments()->create([
                    'file_path' => $path,
                    'type' => 'after'
                ]);
            }

            // Notify about Completion photos
            $this->sendNotification($order->user_id, [
                'type' => \App\Models\Notification::TYPE_ORDER_PHOTOS,
                'title_ar' => 'تم إضافة صور إتمام العمل',
                'title_en' => 'Completion photos added',
                'body_ar' => 'قام الفني بإضافة صور إتمام العمل لطلبك رقم ' . $order->order_number,
                'body_en' => 'The technician added completion photos for your order #' . $order->order_number,
                'data' => ['order_id' => $order->id, 'photo_type' => 'after']
            ]);
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

        // Calculate Final Grand Total using separate rates
        $basePrice = $order->service->price ?? 50;
        $serviceTaxRate = (float) \App\Models\Setting::getByKey('vat_rate_services', 15) / 100;
        $partsTaxRate = (float) \App\Models\Setting::getByKey('vat_rate_parts', 15) / 100;
        
        // Sum up existing parts if not provided in request (or overwrite with new ones)
        if (empty($sparePartsMetadata) && $order->spare_parts_metadata) {
            $sparePartsMetadata = $order->spare_parts_metadata;
            foreach ($sparePartsMetadata as $part) {
                $sparePartsTotal += $part['total'];
            }
        }

        $serviceTax = $basePrice * $serviceTaxRate;
        $partsTax = $sparePartsTotal * $partsTaxRate;

        $subTotal = $basePrice + $sparePartsTotal;
        $tax = $serviceTax + $partsTax;
        $finalGrandTotal = $subTotal + $tax;

        // Re-verify payment against the final calculated total
        $totalPaid = $order->payments()->where('status', 'completed')->sum('amount');
        
        $roundedFinalTotal = round((float)$finalGrandTotal, 2);
        $roundedTotalPaid = round((float)$totalPaid, 2);

        $tolerance = 1.0;
        if (round($roundedTotalPaid, 2) < round($roundedFinalTotal - $tolerance, 2)) {
            \Illuminate\Support\Facades\Log::warning("Order #{$order->order_number} payment failure: Paid={$roundedTotalPaid}, Required={$roundedFinalTotal}");
            return response()->json([
                'status' => false, 
                'message' => __('Please ensure the invoice is paid before finishing the work. Required: :total SAR, Paid: :paid SAR', [
                    'total' => number_format($roundedFinalTotal, 2),
                    'paid' => number_format($roundedTotalPaid, 2)
                ])
            ], 422);
        }

        // Update Order
        $order->status = 'completed';
        $order->sub_status = null;
        $order->spare_parts_metadata = $sparePartsMetadata;
        $order->total_price = $finalGrandTotal; 
        
        if ($request->filled('client_signature') && Str::startsWith($request->client_signature, 'data:image')) {
            $order->client_signature = $this->uploadBase64Image($request->client_signature, 'signatures');
        } elseif ($request->filled('client_signature')) {
            $order->client_signature = $request->client_signature;
        }
        
        $order->save();

        // Sync appointment status to completed
        \App\Models\Appointment::where('order_id', $order->id)
            ->update(['status' => 'completed']);

        // Mark technician as available again
        if ($order->technician) {
            $order->technician->update(['availability_status' => 'available']);
        }

        // --- Calculate and Record Platform Profit using separate rates ---
        $serviceCommissionRate = (float) \App\Models\Setting::getByKey('platform_commission_services', 15);
        $partsCommissionRate = (float) \App\Models\Setting::getByKey('platform_commission_parts', 15);

        $serviceProfit = $basePrice * ($serviceCommissionRate / 100);
        $partsProfit = $sparePartsTotal * ($partsCommissionRate / 100);
        $profitAmount = $serviceProfit + $partsProfit;

        if ($profitAmount > 0) {
            \App\Models\PlatformProfit::create([
                'order_id' => $order->id,
                'fees'     => $serviceCommissionRate, // Primary rate
                'amount'   => round($profitAmount, 2),
            ]);
        }

        // --- Credit Company/Technician Wallet ---
        if ($order->maintenance_company_id) {
            $company = \App\Models\MaintenanceCompany::find($order->maintenance_company_id);
            if ($company && $company->user_id && $order->total_price > 0) {
                $companyShare = $order->total_price - $profitAmount;

                // Update company wallet balance
                $companyUser = \App\Models\User::find($company->user_id);
                if ($companyUser) {
                    $companyUser->wallet_balance = ($companyUser->wallet_balance ?? 0) + $companyShare;
                    $companyUser->save();
                }

                \App\Models\WalletTransaction::create([
                    'user_id'        => $company->user_id,
                    'amount'         => round($companyShare, 2),
                    'type'           => 'deposit',
                    'note'           => 'تحصيل دفعة للطلب رقم ' . $order->order_number,
                    'reference_id'   => $order->id,
                    'reference_type' => Order::class,
                ]);
            }
        } elseif ($order->technician_id) {
            // For independent technicians (not in a company)
            $tech = \App\Models\Technician::find($order->technician_id);
            if ($tech && $tech->user_id && $order->total_price > 0) {
                $techShare = $order->total_price - $profitAmount;

                $techUser = \App\Models\User::find($tech->user_id);
                if ($techUser) {
                    $techUser->wallet_balance = ($techUser->wallet_balance ?? 0) + $techShare;
                    $techUser->save();
                }

                \App\Models\WalletTransaction::create([
                    'user_id'        => $tech->user_id,
                    'amount'         => round($techShare, 2),
                    'type'           => 'deposit',
                    'note'           => 'تحصيل دفعة للطلب رقم ' . $order->order_number,
                    'reference_id'   => $order->id,
                    'reference_type' => Order::class,
                ]);
            }
        }

        $this->sendNotification($order->user_id, [
            'type' => \App\Models\Notification::TYPE_WORK_FINISHED,
            'title_ar' => 'تم الانتهاء من العمل',
            'title_en' => 'Work Completed',
            'body_ar' => 'لقد قام الفني بإنهاء العمل بنجاح. يمكنك الآن مراجعة الفاتورة وتقييم الخدمة',
            'body_en' => 'The technician has successfully finished the work. You can now review the invoice and rate the service',
            'data' => [
                'order_id' => $order->id,
                'technician_id' => $order->technician_id,
                'can_rate' => true
            ]
        ]);

        return response()->json([
            'status' => true, 
            'message' => __('Work finished successfully'), 
            'data' => $order
        ]);
    }

    private function uploadBase64Image($base64String, $folder = 'uploads')
    {
        try {
            $image_service_str = $base64String;
            $after_comma = strpos($image_service_str, ',') + 1;
            $image_base64_decod = base64_decode(substr($image_service_str, $after_comma));
            
            $extension = 'png'; // Default
            if (preg_match('/^data:image\/(\w+);base64,/', $base64String, $type)) {
                $extension = strtolower($type[1]);
            }

            $filename = time() . '_' . Str::random(10) . '.' . $extension;
            $directory = public_path($folder);
            
            if (!file_exists($directory)) {
                mkdir($directory, 0775, true);
            }

            $path = $directory . '/' . $filename;
            file_put_contents($path, $image_base64_decod);
            
            return $folder . '/' . $filename;
        } catch (\Exception $e) {
            return $base64String; // Fallback to raw if failed
        }
    }

    public function cancel(Request $request, $id)
    {
        $request->validate([
            'cancellation_reason' => 'required|string|max:500',
        ]);

        $order = $this->getOrderIfAuthorized($id);
        if (!$order) return response()->json(['status' => false, 'message' => __('Order not found')], 404);

        if (in_array($order->status, ['completed', 'cancelled', 'rejected'])) {
            return response()->json(['status' => false, 'message' => 'Order is already closed'], 422);
        }

        return \Illuminate\Support\Facades\DB::transaction(function () use ($order, $request) {
            // 1. Mark as cancelled
            $order->update([
                'status' => 'cancelled',
                'rejection_reason' => $request->cancellation_reason,
            ]);

            // Sync appointment status to cancelled
            \App\Models\Appointment::where('order_id', $order->id)
                ->update(['status' => 'cancelled']);

            // Reset technician status if order was in progress
            if ($order->technician) {
                $order->technician->update(['availability_status' => 'available']);
            }

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
        $order = $this->getOrderIfAuthorized($id);
        if (!$order) return response()->json(['status' => false, 'message' => __('Order not found')], 404);
        $order->delete();
        return response()->json(['status' => true, 'message' => 'Order deleted']);
    }

    public function resend(Request $request, $id)
    {
        $oldOrder = $this->getOrderIfAuthorized($id);
        if (!$oldOrder) return response()->json(['status' => false, 'message' => __('Order not found')], 404);

        if (!in_array($oldOrder->status, ['cancelled', 'completed', 'rejected'])) {
            return response()->json(['status' => false, 'message' => 'Can only resend cancelled/completed orders'], 422);
        }

        $lastOrderCheck = Order::latest('id')->first();
        $nextId = $lastOrderCheck ? $lastOrderCheck->id + 1 : 1;

        $newOrder = Order::create([
            'order_number' => $nextId,
            'user_id' => $oldOrder->user_id,
            'city_id' => $oldOrder->city_id,
            'service_id' => $oldOrder->service_id,
            'status' => 'new',
            'total_price' => $oldOrder->total_price,
            'payment_method' => $oldOrder->payment_method,
            'scheduled_at' => $request->scheduled_at ?? now()->addDay(),
            'address' => $oldOrder->address,
            'notes' => $oldOrder->notes,
            'latitude' => $oldOrder->latitude,
            'longitude' => $oldOrder->longitude,
        ]);

        return response()->json(['status' => true, 'message' => 'Order resent', 'data' => $newOrder]);
    }

    public function reschedule(Request $request, $id)
    {
        $order = $this->getOrderIfAuthorized($id);
        if (!$order) return response()->json(['status' => false, 'message' => __('Order not found')], 404);

        $user = auth()->user();

        // Check if there is already a pending reschedule request
        if ($order->sub_status === 'reschedule_requested') {
            return response()->json(['status' => false, 'message' => __('There is already a pending reschedule request for this order.')], 422);
        }

        if (!$order->can_reschedule) {
            return response()->json(['status' => false, 'message' => __('This order cannot be rescheduled in its current status.')], 422);
        }

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'scheduled_at' => 'required|date|after:now',
        ], [
            'scheduled_at.after' => __('The scheduled date must be a future date.'),
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        // Save proposed schedule instead of applying directly
        $order->update([
            'proposed_scheduled_at' => $request->scheduled_at,
            'reschedule_requested_by' => $user->type,
            'sub_status' => 'reschedule_requested',
        ]);

        // Notify the other party
        if ($user->type === 'client' || $user->type === 'individual' || $user->type === 'corporate_customer') {
            // Client rescheduled -> Notify technician if assigned
            if ($order->technician_id) {
                $this->sendNotification($order->technician->user_id, [
                    'type' => \App\Models\Notification::TYPE_ORDER_RESCHEDULED ?? 'order_rescheduled',
                    'title_ar' => 'طلب تعديل موعد الطلب',
                    'title_en' => 'Order Reschedule Request',
                    'body_ar' => "اقترح العميل تعديل موعد الطلب رقم {$order->order_number} إلى " . \Carbon\Carbon::parse($order->proposed_scheduled_at)->format('Y-m-d H:i') . ". يرجى الموافقة أو الرفض.",
                    'body_en' => "The client requested to reschedule order #{$order->order_number} to " . \Carbon\Carbon::parse($order->proposed_scheduled_at)->format('Y-m-d H:i') . ". Please accept or reject.",
                    'data' => ['order_id' => $order->id]
                ]);
            }
        } else {
            // Tech or Company rescheduled -> Notify Client
            $this->sendNotification($order->user_id, [
                'type' => \App\Models\Notification::TYPE_ORDER_RESCHEDULED ?? 'order_rescheduled',
                'title_ar' => 'طلب تعديل الموعد من قبل الفني',
                'title_en' => 'Reschedule Request by Technician',
                'body_ar' => "اقترح الفني تعديل موعد الطلب رقم {$order->order_number} إلى " . \Carbon\Carbon::parse($order->proposed_scheduled_at)->format('Y-m-d H:i') . ". نعتذر عن أي إزعاج، يرجى الموافقة أو الرفض.",
                'body_en' => "The technician requested to reschedule order #{$order->order_number} to " . \Carbon\Carbon::parse($order->proposed_scheduled_at)->format('Y-m-d H:i') . ". Sorry for any inconvenience, please accept or reject.",
                'data' => ['order_id' => $order->id]
            ]);
        }

        return response()->json(['status' => true, 'message' => __('Reschedule request sent successfully awaiting approval.'), 'data' => $order]);
    }

    public function respondToReschedule(Request $request, $id)
    {
        $order = $this->getOrderIfAuthorized($id);
        if (!$order) return response()->json(['status' => false, 'message' => __('Order not found')], 404);

        if (!in_array($order->sub_status, ['reschedule_requested', 'additional_visit']) || !$order->proposed_scheduled_at) {
            return response()->json(['status' => false, 'message' => __('No pending reschedule or additional visit request found for this order.')], 422);
        }

        $user = auth()->user();

        // Ensure the person responding is NOT the one who requested it
        // A client cannot respond to a client request.
        $isClient = in_array($user->type, ['client', 'individual', 'corporate_customer']);
        $isTechOrCompany = in_array($user->type, ['technician', 'maintenance_company']);
        $requestWasByClient = in_array($order->reschedule_requested_by, ['client', 'individual', 'corporate_customer']);
        
        if (($isClient && $requestWasByClient) || ($isTechOrCompany && !$requestWasByClient)) {
            return response()->json(['status' => false, 'message' => __('You cannot respond to your own reschedule request.')], 403);
        }

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'action' => 'required|in:accept,reject',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }

        if ($request->action === 'accept') {
            // Apply the proposed change
            $order->update([
                'scheduled_at' => $order->proposed_scheduled_at,
                'proposed_scheduled_at' => null,
                'reschedule_requested_by' => null,
                'sub_status' => null,
            ]);

            // Update the reschedule notification type to hide action buttons in the app
            $this->updateNotificationType($user->id, $order->id, 'order_reschedule_accepted', \App\Models\Notification::TYPE_ORDER_RESCHEDULED);

            // Update related Appointment records
            \App\Models\Appointment::where('order_id', $order->id)->update([
                'appointment_date' => $order->scheduled_at,
            ]);

            // Notify Initiator of Acceptance
            $notifyUserId = $requestWasByClient ? $order->user_id : ($order->technician->user_id ?? ($order->maintenanceCompany->user_id ?? null));
            if ($notifyUserId) {
                $this->sendNotification($notifyUserId, [
                    'type' => \App\Models\Notification::TYPE_ORDER_RESCHEDULED ?? 'order_rescheduled',
                    'title_ar' => 'تمت الموافقة على تعديل الموعد',
                    'title_en' => 'Reschedule Request Accepted',
                    'body_ar' => "تمت الموافقة على الموعد الجديد للطلب رقم {$order->order_number}.",
                    'body_en' => "The new scheduled time for order #{$order->order_number} has been accepted.",
                    'data' => ['order_id' => $order->id]
                ]);
            }

            return response()->json(['status' => true, 'message' => __('Reschedule request accepted successfully.'), 'data' => $order]);
        } else {
            // Handle Reject
            $order->update([
                'proposed_scheduled_at' => null,
                'reschedule_requested_by' => null,
                'sub_status' => null,
            ]);

            // Update the reschedule notification type to hide action buttons in the app
            $this->updateNotificationType($user->id, $order->id, 'order_reschedule_rejected', \App\Models\Notification::TYPE_ORDER_RESCHEDULED);

            // Notify Initiator of Rejection
            $notifyUserId = $requestWasByClient ? $order->user_id : ($order->technician->user_id ?? ($order->maintenanceCompany->user_id ?? null));
            if ($notifyUserId) {
                $this->sendNotification($notifyUserId, [
                    'type' => \App\Models\Notification::TYPE_ORDER_RESCHEDULED ?? 'order_rescheduled',
                    'title_ar' => 'تم رفض طلب تعديل الموعد',
                    'title_en' => 'Reschedule Request Rejected',
                    'body_ar' => "تم رفض الموعد المقترح للطلب رقم {$order->order_number}. بقي الموعد الأصلي كما هو.",
                    'body_en' => "The proposed time for order #{$order->order_number} was rejected. The original time remains.",
                    'data' => ['order_id' => $order->id]
                ]);
            }

            return response()->json(['status' => true, 'message' => __('Reschedule request rejected.'), 'data' => $order]);
        }
    }

    private function getOrderIfAuthorized($id)
    {
        $user = auth()->user();
        $query = Order::where('id', $id);

        if ($user->type === 'technician') {
            $technician = $user->technician;
            if (!$technician) return null;
            $query->where('technician_id', $technician->id);
        } elseif ($user->type === 'maintenance_company') {
            $company = $user->maintenanceCompany;
            if (!$company) return null;
            $query->where('maintenance_company_id', $company->id);
        } else {
            // Individual / Corporate / etc.
            $query->where('user_id', $user->id);
        }

        return $query->first();
    }

    public function getInvoice($id)
    {
        $order = Order::with(['service', 'user'])->find($id);
        if (!$order) return response()->json(['status' => false, 'message' => 'Order not found'], 404);

        $serviceTaxRate = (float) \App\Models\Setting::getByKey('vat_rate_services', 15) / 100;
        $partsTaxRate = (float) \App\Models\Setting::getByKey('vat_rate_parts', 15) / 100;
        $serviceCommissionRate = (float) \App\Models\Setting::getByKey('platform_commission_services', 15);
        $partsCommissionRate = (float) \App\Models\Setting::getByKey('platform_commission_parts', 15);

        $serviceBase = $order->service->price ?? 50;
        $serviceTax = $serviceBase * $serviceTaxRate;
        $serviceCommission = $serviceBase * ($serviceCommissionRate / 100);
        $serviceCompanyShare = $serviceBase - $serviceCommission;

        $sparePartsBase = 0;
        if (is_array($order->spare_parts_metadata)) {
            foreach ($order->spare_parts_metadata as $part) {
                $sparePartsBase += isset($part['total']) ? $part['total'] : ($part['qty'] * $part['price']);
            }
        }
        $sparePartsTax = $sparePartsBase * $partsTaxRate;
        $sparePartsCommission = $sparePartsBase * ($partsCommissionRate / 100);
        $sparePartsCompanyShare = $sparePartsBase - $sparePartsCommission;

        $totalBase = $serviceBase + $sparePartsBase;
        $totalTax = $serviceTax + $sparePartsTax;
        $totalCommission = $serviceCommission + $sparePartsCommission;
        $totalCompanyShare = $serviceCompanyShare + $sparePartsCompanyShare;
        $grandTotal = $totalBase + $totalTax;

        $isPaid = $order->payments()->where('status', 'completed')->exists();

        return response()->json([
            'status' => true,
            'data' => [
                'invoice_number' => 'INV-' . $order->order_number,
                'date' => $order->created_at->format('Y-m-d'),
                'client' => $order->user->name,
                'is_paid' => $isPaid,
                'service' => $order->service->name_ar ?? 'خدمة',
                'service_amount' => number_format($serviceBase, 2),
                'service_tax' => number_format($serviceTax, 2),
                'service_commission' => number_format($serviceCommission, 2),
                'service_company_share' => number_format($serviceCompanyShare, 2),
                'spare_parts_amount' => number_format($sparePartsBase, 2),
                'spare_parts_tax' => number_format($sparePartsTax, 2),
                'spare_parts_commission' => number_format($sparePartsCommission, 2),
                'spare_parts_company_share' => number_format($sparePartsCompanyShare, 2),
                'base_amount' => number_format($totalBase, 2),
                'tax' => number_format($totalTax, 2),
                'total_commission' => number_format($totalCommission, 2),
                'total_company_share' => number_format($totalCompanyShare, 2),
                'total' => number_format($grandTotal, 2),
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

        $loc = \App\Models\TechnicianLocation::where('technician_id', $order->technician->user_id)->first();
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
        if ($user && $user->type === 'technician') {
            return $this->technicianAccept($request, $id);
        }

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

        // --- DUPLICATE PREVENTION ---
        // If the order is already assigned and no new information is provided, 
        // return success without resending notifications.
        if ($order->status !== 'new') {
            $isSameTech = $request->technician_id == $order->technician_id;
            $isSameCompany = ($company->id ?? null) == $order->maintenance_company_id;
            
            $isSameScheduled = true;
            if ($request->filled('scheduled_at')) {
                if ($order->scheduled_at) {
                    $reqTime = \Carbon\Carbon::parse($request->scheduled_at)->toDateTimeString();
                    $ordTime = $order->scheduled_at->toDateTimeString();
                    $isSameScheduled = ($reqTime === $ordTime);
                }
            }

            if ($isSameTech && $isSameCompany && $isSameScheduled) {
                 return response()->json(['status' => true, 'message' => 'Order already accepted with these details', 'data' => $order]);
            }
        }
        // ----------------------------

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
            // Validate the technician: Must belong to THIS company (if explicitly provided)
            if ($request->filled('technician_id')) {
                $requestedTechId = $request->technician_id;
                $checkTech = \App\Models\Technician::where('id', $requestedTechId)
                    ->where('maintenance_company_id', $company->id)
                    ->first();
                
                if ($checkTech) {
                    $order->technician_id = $checkTech->id;
                    $order->assigned_at = now();
                    // Mark as waiting for technician's acceptance
                    $order->sub_status = 'pending_tech_acceptance';
                    
                    // Sync with Appointment
                    \App\Models\Appointment::where('order_id', $order->id)->update([
                        'technician_id' => $checkTech->id,
                        'appointment_date' => $request->scheduled_at ?? $order->scheduled_at,
                        'status' => 'scheduled'
                    ]);
                } else {
                    return response()->json(['status' => false, 'message' => 'Selected technician does not belong to your company'], 422);
                }
            } else {
                // Company accepted without assigning a technician yet (clear auto-assigned tech)
                $order->technician_id = null;
                $order->sub_status = null;

                // Sync with Appointment
                \App\Models\Appointment::where('order_id', $order->id)->update([
                    'technician_id' => null,
                    'appointment_date' => $request->scheduled_at ?? $order->scheduled_at,
                    'status' => 'scheduled'
                ]);
            }
        }
        
        if ($technician) {
            $order->technician_id = $technician->id;
            $order->assigned_at = now();
        }

            $duration = (int) \App\Models\Setting::getByKey('order_acceptance_duration', 15);
        
            // Notify Technician
            $tech = \App\Models\Technician::find($order->technician_id);
            if ($tech && $tech->user_id) {
                 $this->sendNotification($tech->user_id, [
                    'type' => \App\Models\Notification::TYPE_NEW_ORDER ?? 'new_order',
                    'title_ar' => 'مهمة جديدة',
                    'title_en' => 'New Task Assigned',
                    'body_ar' => "تم تعيين مهمة جديدة لك، يرجى القبول أو الرفض خلال $duration دقيقة",
                    'body_en' => "You have been assigned a new task. Please accept or reject within $duration minutes",
                    'data' => ['order_id' => $order->id]
                ]);
            }
        if ($request->scheduled_at) {
            $order->scheduled_at = $request->scheduled_at;
        }
        
        $order->save();

        // Update the new_order notification type to hide action buttons in the app
        $this->updateNotificationType($user->id, $order->id, \App\Models\Notification::TYPE_ORDER_ACCEPTED);

        // Prepare Notification Text
        $titleAr = 'تم قبول طلبك';
        $bodyAr = 'قامت شركة الصيانة بقبول طلبك وتحديد موعد للزيارة';
        $titleEn = 'Your order was accepted';
        $bodyEn = 'The maintenance company has accepted your order and scheduled a visit';
        $notifType = \App\Models\Notification::TYPE_ORDER_ACCEPTED;

        if ($request->technician_id) {
            $titleAr = 'تم تعيين فني لطلبك';
            $bodyAr = 'قامت شركة الصيانة بتعيين فني لطلبك، وفي انتظار تأكيده للموعد.';
            $titleEn = 'Technician assigned to your order';
            $bodyEn = 'The maintenance company has assigned a technician to your order and is waiting for his confirmation.';
            $notifType = \App\Models\Notification::TYPE_TECHNICIAN_ASSIGNED;
        }

        $this->sendNotification($order->user_id, [
            'type' => $notifType,
            'title_ar' => $titleAr,
            'title_en' => $titleEn,
            'body_ar' => $bodyAr,
            'body_en' => $bodyEn,
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

        // Expiration check using dynamic duration
        $duration = (int) \App\Models\Setting::getByKey('order_acceptance_duration', 15);
        if ($order->assigned_at && $order->assigned_at->diffInMinutes(now()) > $duration) {
            return response()->json(['status' => false, 'message' => "Order request expired ($duration minutes limit)"], 422);
        }

        if (!in_array($order->status, ['scheduled', 'new'])) {
             if ($order->status === 'accepted' || $order->status === 'in_progress') {
                 return response()->json(['status' => false, 'message' => 'Order already accepted'], 422);
             }
        }

        // Only orders waiting for tech acceptance can be accepted here
        if ($order->sub_status !== 'pending_tech_acceptance') {
            return response()->json(['status' => false, 'message' => 'This order is not pending your acceptance'], 422);
        }

        // Technician accepted -> move to 'accepted' and clear the pending sub_status
        $order->status = 'accepted';
        $order->sub_status = null;
        $order->save();

        // Update the new_order notification type to hide action buttons in the app
        $this->updateNotificationType($user->id, $order->id, \App\Models\Notification::TYPE_ORDER_ACCEPTED);

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

        // Notify Client that the technician has confirmed
        $this->sendNotification($order->user_id, [
            'type' => \App\Models\Notification::TYPE_ORDER_ACCEPTED,
            'title_ar' => 'تأكيد الموعد من قبل الفني',
            'title_en' => 'Appointment Confirmed by Technician',
            'body_ar' => "قام الفني {$technician->name_ar} بتأكيد موعد الزيارة لطلبك رقم {$order->order_number}. الفني سيصلك في الموعد المحدد.",
            'body_en' => "Technician {$technician->name_en} has confirmed the appointment for your order #{$order->order_number}. The technician will arrive at the scheduled time.",
            'data' => ['order_id' => $order->id]
        ]);

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

        if (!$this->checkRejectionLimit($user)) {
            $limit = (int) \App\Models\Setting::getByKey('rejection_block_threshold', 5);
            return response()->json([
                'status' => false,
                'message' => __('You have reached the maximum allowed limit for rejecting orders (:limit orders). You cannot reject this order. Please contact support.', ['limit' => $limit])
            ], 422);
        }

        // 15-minute expiration check (Still relevant? If expired, can they refuse? Yes, to clear it.)
        // But maybe we don't block refusal if expired.

        $request->validate([
            'rejection_reason' => 'nullable|string|max:500',
        ]);

        $order->technician_id = null; // Unassign technician
        $order->assigned_at = null;   // Clear assignment time
        $order->sub_status = null;    // Clear pending acceptance flag
        $order->rejection_reason = $request->rejection_reason;

        // If the order belonged to a company, keep it 'scheduled' under the company
        // so the company can re-assign another technician.
        // If it was a standalone order (no company), reset it to 'new'.
        if ($order->maintenance_company_id) {
            $order->status = 'scheduled'; // Still accepted by company, needs re-assignment
        } else {
            $order->status = 'new';
        }

        $order->save();

        // Sync with Appointment (clear technician)
        \App\Models\Appointment::where('order_id', $order->id)->update([
            'technician_id' => null,
        ]);

        // Check for penalties (Technician)
        $this->handleRejectionPenalty($order, $user);

        // Update the new_order notification type to hide action buttons in the app
        $this->updateNotificationType($user->id, $order->id, \App\Models\Notification::TYPE_ORDER_REJECTED);

        // Notify Company
        if ($order->maintenance_company_id) {
            $companyUser = \App\Models\MaintenanceCompany::find($order->maintenance_company_id)->user_id ?? null;
            if ($companyUser) {
                 $this->sendNotification($companyUser, [
                    'type' => \App\Models\Notification::TYPE_ORDER_REJECTED,
                    'title_ar' => 'الفني رفض المهمة',
                    'title_en' => 'Technician Rejected Task',
                    'body_ar' => "قام الفني {$technician->name_ar} برفض المهمة رقم {$order->order_number}." . ($request->rejection_reason ? " السبب: {$request->rejection_reason}" : ""),
                    'body_en' => "Technician {$technician->name_en} rejected task #{$order->order_number}." . ($request->rejection_reason ? " Reason: {$request->rejection_reason}" : ""),
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

        if (!$this->checkRejectionLimit($user)) {
            $limit = (int) \App\Models\Setting::getByKey('rejection_block_threshold', 5);
            return response()->json([
                'status' => false,
                'message' => __('You have reached the maximum allowed limit for rejecting orders (:limit orders). You cannot reject this order. Please contact support.', ['limit' => $limit])
            ], 422);
        }

        $request->validate([
            'rejection_reason' => 'nullable|string|max:500',
        ]);

        $order->status = 'rejected';
        $order->rejection_reason = $request->rejection_reason;
        $order->save();

        // Check for penalties (Company)
        $this->handleRejectionPenalty($order, $user);

        // Update the new_order notification type to hide action buttons in the app
        $this->updateNotificationType($user->id, $order->id, \App\Models\Notification::TYPE_ORDER_REJECTED);

        // Reset technician status
        if ($order->technician) {
            $order->technician->update(['availability_status' => 'available']);
        }

        // Notify client
        $reasonAr = $order->rejection_reason ? (' للسبب التالي: ' . $order->rejection_reason) : '';
        $reasonEn = $order->rejection_reason ? (' for: ' . $order->rejection_reason) : '';

        $this->sendNotification($order->user_id, [
            'type' => \App\Models\Notification::TYPE_ORDER_REJECTED,
            'title_ar' => 'تم رفض طلبك',
            'title_en' => 'Your order was rejected',
            'body_ar' => 'نعتذر، تم رفض طلبك' . $reasonAr,
            'body_en' => 'Sorry, your order was rejected' . $reasonEn,
            'data' => ['order_id' => $order->id]
        ]);

        return response()->json(['status' => true, 'message' => 'Order refused', 'data' => $order]);
    }

    private function notifyNewOrder($order)
    {
        // For companies that might provide this service
        $companies = \App\Models\MaintenanceCompany::whereHas('services', function($q) use ($order) {
            $q->where('services.id', $order->service_id);
        })->where('city_id', $order->city_id)->distinct()->get();

        // Extra layer of protection in case of duplicate user_ids
        $companies = $companies->unique('user_id');

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
        
        if ($user) {
            // Always save to database for in-app notification history
            \App\Models\Notification::create([
                'user_id' => $userId,
                'type' => $details['type'],
                'target_audience' => 'individual_user',
                'title_ar' => $details['title_ar'],
                'title_en' => $details['title_en'],
                'body_ar' => $details['body_ar'],
                'body_en' => $details['body_en'],
                'data' => $details['data'] ?? [],
                'status' => 'sent',
                'is_read' => false
            ]);

            // Only send FCM push if user has a token AND has not disabled notifications
            if ($user->fcm_token && $user->notification_enabled) {
                $user->sendFcmNotification($details, false);
            }
        }
    }

    private function updateNotificationType($userId, $orderId, $newType, $oldType = null)
    {
        $oldTypes = $oldType ? (array) $oldType : [\App\Models\Notification::TYPE_NEW_ORDER];

        $notifications = \App\Models\Notification::where('user_id', $userId)
            ->whereIn('type', $oldTypes)
            ->get();
            
        foreach ($notifications as $notification) {
            if (isset($notification->data['order_id']) && $notification->data['order_id'] == $orderId) {
                $notification->update(['type' => $newType]);
            }
        }
    }

    private function formatTechnicianData($technician)
    {
        if (!$technician) return null;

        $locale = app()->getLocale();
        return [
            'id' => $technician->id,
            'name' => $technician->user?->name ?? ($locale == 'ar' ? $technician->name_ar : ($technician->name_en ?? $technician->name_ar)),
            'maintenance_company_id' => $technician->maintenance_company_id,
            'company_name' => $locale == 'ar' ? ($technician->maintenanceCompany?->company_name_ar ?? $technician->maintenanceCompany?->name) : ($technician->maintenanceCompany?->company_name_en ?? $technician->maintenanceCompany?->name),
            'specialty' => $technician->category ? (__('Specialized in') . ' ' . ($locale == 'ar' ? $technician->category->name_ar : ($technician->category->name_en ?? $technician->category->name_ar))) : null,
            'order_count' => $technician->order_count ?? 0,
            'image' => $technician->user?->avatar_url ?? asset('assets/images/default-avatar.png'),
            'bio_en' => $technician->bio_en,
            'bio_ar' => $technician->bio_ar,
            'service_id' => $technician->service_id,
            'category_id' => $technician->category_id,
            'avg_rating' => round($technician->reviews_avg_rating ?? 0, 1),
            'review_count' => $technician->reviews_count ?? 0,
            'exper_years' => $technician->years_experience ?? 0,
        ];
    }

    /**
     * Add attachments to an order (Anytime)
     */
    public function addAttachment(Request $request, $id)
    {
        $order = $this->getOrderIfAuthorized($id);
        if (!$order) return response()->json(['status' => false, 'message' => __('Order not found')], 404);

        $request->validate([
            'attachments' => 'required|array',
            'attachments.*' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'type' => 'nullable|string|in:before,after,during'
        ]);

        $type = $request->type ?? 'during';

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $this->uploadAttachment($file);
                $order->attachments()->create([
                    'file_path' => $path,
                    'type' => $type
                ]);
            }

            // Notify Client
            $this->sendNotification($order->user_id, [
                'type' => \App\Models\Notification::TYPE_ORDER_PHOTOS,
                'title_ar' => 'تم إضافة مرفقات جديدة',
                'title_en' => 'New attachments added',
                'body_ar' => 'قام الفني بإضافة مرفقات / صور جديدة لطلبك رقم ' . $order->order_number,
                'body_en' => 'The technician added new attachments/photos to your order #' . $order->order_number,
                'data' => ['order_id' => $order->id, 'attachment_type' => $type]
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => __('Photos added successfully'),
            'data' => $order->load('attachments')
        ]);
    }

    /**
     * Delete a specific attachment from an order
     */
    public function deleteAttachment($id, $attachment_id)
    {
        $order = $this->getOrderIfAuthorized($id);
        if (!$order) return response()->json(['status' => false, 'message' => __('Order not found')], 404);

        $attachment = $order->attachments()->find($attachment_id);
        if (!$attachment) {
            return response()->json(['status' => false, 'message' => 'Attachment not found'], 404);
        }

        // Delete from public folder
        if ($attachment->file_path && file_exists(public_path($attachment->file_path))) {
            unlink(public_path($attachment->file_path));
        }

        $attachment->delete();

        return response()->json([
            'status' => true,
            'message' => __('Photo deleted successfully'),
            'data' => $order->load('attachments')
        ]);
    }

    private function isOrderExpired($order)
    {
        return $order->scheduled_at && 
               $order->scheduled_at->isPast() && 
               $order->scheduled_at->diffInHours(now()) > 24;
    }

    private function uploadAttachment($file)
    {
        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $dir = public_path('uploads/order_attachments');
        if (!is_dir($dir)) mkdir($dir, 0775, true);
        $file->move($dir, $filename);
        return 'uploads/order_attachments/' . $filename;
    }
}
