<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $query = Notification::query();

        // Search
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title_ar', 'like', "%{$search}%")
                  ->orWhere('title_en', 'like', "%{$search}%")
                  ->orWhere('body_ar', 'like', "%{$search}%")
                  ->orWhere('body_en', 'like', "%{$search}%");
            });
        }

        // Filters
        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->filled('target_audience')) {
            $query->where('target_audience', $request->input('target_audience'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Date Filter
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'newest');
        if ($sortBy === 'newest' || $sortBy === 'newest_date') {
            $query->latest();
        } elseif ($sortBy === 'oldest') {
            $query->oldest();
        } elseif ($sortBy === 'name') {
            $query->orderBy('title_ar', 'asc');
        } else {
            $query->latest();
        }

        // Pagination
        $perPage = $request->input('per_page', 10);
        $items = $query->with('user')->paginate($perPage)->withQueryString();

        // Statistics
        $lastWeek = Carbon::now()->subWeek();
        $getStats = function ($status = null) use ($lastWeek) {
            $currentCount = Notification::when($status, fn($q) => $q->where('status', $status))->count();
            $previousCount = Notification::when($status, fn($q) => $q->where('status', $status))->where('created_at', '<', $lastWeek)->count();

            $percentage = 0;
            if ($previousCount > 0) {
                $percentage = (($currentCount - $previousCount) / $previousCount) * 100;
            } elseif ($currentCount > 0) {
                $percentage = 100;
            }

            return [
                'count' => $currentCount,
                'percentage' => ($percentage >= 0 ? '+' : '') . number_format($percentage, 2) . '%',
                'percentage_val' => $percentage
            ];
        };

        $statistics = [
            'total' => $getStats(),
            'sent' => $getStats('sent'),
            'unsent' => $getStats('unsent'),
            'scheduled' => $getStats('scheduled'),
        ];

        return view('admin.notifications.index', compact('items', 'statistics'));
    }

    public function create()
    {
        return view('admin.notifications.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title_ar' => 'required|string',
            'title_en' => 'required|string',
            'body_ar' => 'required|string',
            'body_en' => 'required|string',
            'target_audience' => ['required', Rule::in(Notification::TARGET_AUDIENCES)],
            'phone' => 'required_if:target_audience,individual_user|nullable|string',
            'type' => ['required', Rule::in(Notification::TYPES)],
            'status' => ['nullable', Rule::in(Notification::STATUSES)],
            'scheduled_at' => 'nullable|date',
        ]);

        $data = $request->except('phone');

        if ($request->target_audience === 'individual_user' && $request->filled('phone')) {
            $user = \App\Models\User::where('phone', $request->phone)
                      ->orWhere('phone', 'like', '%' . ltrim($request->phone, '0'))
                      ->first();

            if (!$user) {
                return back()->withInput()->withErrors(['phone' => __('No user found with this phone number.')]);
            }
            $data['user_id'] = $user->id;
        }

        $notification = Notification::create($data);

        // Send FCM if status is 'sent'
        if ($notification->status === 'sent') {
            $fcmService = app(\App\Services\FcmService::class);
            $details = [
                'type' => $notification->type,
                'title_ar' => $notification->title_ar,
                'title_en' => $notification->title_en,
                'body_ar' => $notification->body_ar,
                'body_en' => $notification->body_en,
                'data' => $notification->data ?? [],
            ];

            if ($notification->target_audience === 'individual_user' && $notification->user_id) {
                if ($notification->user) {
                    $fcmService->sendToUser($notification->user, $details, false);
                }
            } else {
                $query = \App\Models\User::whereNotNull('fcm_token')
                    ->where('fcm_token', '!=', '')
                    ->where(function ($q) {
                        $q->whereNull('notification_enabled')
                          ->orWhere('notification_enabled', true)
                          ->orWhere('notification_enabled', 1);
                    });
                
                switch ($notification->target_audience) {
                    case 'clients':
                        $query->whereIn('type', ['individual', 'corporate_customer']);
                        break;
                    case 'technicians':
                        $query->where('type', 'technician');
                        break;
                    case 'companies':
                        $query->where('type', 'maintenance_company');
                        break;
                    // 'all' doesn't need extra filter
                }
                
                $sentDeviceTokens = [];
                $query->chunk(100, function ($users) use ($fcmService, $details, &$sentDeviceTokens) {
                    foreach ($users as $user) {
                        if ($user->fcm_token && !in_array($user->fcm_token, $sentDeviceTokens)) {
                            $sentDeviceTokens[] = $user->fcm_token;
                            $fcmService->sendToUser($user, $details, false);
                        }
                    }
                });
            }
        }

        return redirect()->route('admin.broadcast-notifications.index')->with('success', __('Notification created successfully and sending process initiated.'));
    }

    public function show($id)
    {
        $item = Notification::findOrFail($id);
        return view('admin.notifications.show', compact('item'));
    }

    public function edit($id)
    {
        $item = Notification::with('user')->findOrFail($id);
        return view('admin.notifications.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $notification = Notification::findOrFail($id);

        $request->validate([
            'title_ar' => 'sometimes|string',
            'title_en' => 'sometimes|string',
            'body_ar' => 'sometimes|string',
            'body_en' => 'sometimes|string',
            'target_audience' => ['sometimes', Rule::in(Notification::TARGET_AUDIENCES)],
            'phone' => 'nullable|string',
            'type' => ['sometimes', Rule::in(Notification::TYPES)],
            'status' => ['sometimes', Rule::in(Notification::STATUSES)],
            'scheduled_at' => 'nullable|date',
        ]);

        $data = $request->except('phone');

        if (isset($data['target_audience'])) {
            if ($data['target_audience'] === 'individual_user' && $request->filled('phone')) {
                $user = \App\Models\User::where('phone', $request->phone)
                          ->orWhere('phone', 'like', '%' . ltrim($request->phone, '0'))
                          ->first();

                if (!$user) {
                    return back()->withInput()->withErrors(['phone' => __('No user found with this phone number.')]);
                }
                $data['user_id'] = $user->id;
            } elseif ($data['target_audience'] !== 'individual_user') {
                $data['user_id'] = null;
            }
        }

        $notification->update($data);
        return redirect()->route('admin.broadcast-notifications.index')->with('success', __('Notification updated successfully.'));
    }

    public function destroy($id)
    {
        $notification = Notification::findOrFail($id);
        $notification->delete();
        return redirect()->route('admin.broadcast-notifications.index')->with('success', __('Notification deleted successfully.'));
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:notifications,id',
        ]);

        Notification::whereIn('id', $request->ids)->delete();

        return redirect()->route('admin.broadcast-notifications.index')->with('success', __('Notifications deleted successfully.'));
    }

    public function send($id)
    {
        $notification = Notification::findOrFail($id);

        $fcmService = app(\App\Services\FcmService::class);
        $details = [
            'type' => $notification->type,
            'title_ar' => $notification->title_ar,
            'title_en' => $notification->title_en,
            'body_ar' => $notification->body_ar,
            'body_en' => $notification->body_en,
            'data' => $notification->data ?? [],
        ];

        if ($notification->target_audience === 'individual_user' && $notification->user_id) {
            if ($notification->user) {
                $fcmService->sendToUser($notification->user, $details, false);
            }
        } else {
            $query = \App\Models\User::whereNotNull('fcm_token')
                ->where('fcm_token', '!=', '')
                ->where(function ($q) {
                    $q->whereNull('notification_enabled')
                      ->orWhere('notification_enabled', true)
                      ->orWhere('notification_enabled', 1);
                });
            
            switch ($notification->target_audience) {
                case 'clients':
                    $query->whereIn('type', ['individual', 'corporate_customer']);
                    break;
                case 'technicians':
                    $query->where('type', 'technician');
                    break;
                case 'companies':
                    $query->where('type', 'maintenance_company');
                    break;
            }
            
            $sentDeviceTokens = [];
            $query->chunk(100, function ($users) use ($fcmService, $details, &$sentDeviceTokens) {
                foreach ($users as $user) {
                    if ($user->fcm_token && !in_array($user->fcm_token, $sentDeviceTokens)) {
                        $sentDeviceTokens[] = $user->fcm_token;
                        $fcmService->sendToUser($user, $details, false);
                    }
                }
            });
        }

        $notification->status = 'sent';
        $notification->save();

        return redirect()->route('admin.broadcast-notifications.index')->with('success', __('Notification sent successfully.'));
    }
}
