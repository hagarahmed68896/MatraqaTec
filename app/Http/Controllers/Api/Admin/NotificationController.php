<?php

namespace App\Http\Controllers\Api\Admin;

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
        // Type: 'alert', 'reminder', 'notification' etc.
        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        // Target Audience: 'clients', 'companies', 'technicians', 'all'
        if ($request->filled('target_audience')) {
            $query->where('target_audience', $request->input('target_audience'));
        }

        // Status: 'sent', 'scheduled', 'not_sent'
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
        $sortColumn = $request->input('sort_by', 'created_at'); // default: created_at
        $sortDirection = $request->input('sort_order', 'desc'); // default: desc

        // Map frontend sort keys to DB columns if needed, e.g., 'name' -> 'title_ar'
        if ($sortColumn === 'name') {
            $sortColumn = 'title_ar'; // or title_en based on lang
        }
        $query->orderBy($sortColumn, $sortDirection);

        // Pagination
        $perPage = $request->input('per_page', 10);
        $notifications = $query->paginate($perPage);

        // Statistics
        $lastWeek = Carbon::now()->subWeek();
        $getStats = function ($status = null) use ($lastWeek) {
            $query = Notification::query();
            if ($status) {
                $query->where('status', $status);
            }
            $currentCount = $query->count();
            $previousCount = $query->where('created_at', '<=', $lastWeek)->count();

            $percentage = 0;
            if ($previousCount > 0) {
                $percentage = (($currentCount - $previousCount) / $previousCount) * 100;
            } elseif ($currentCount > 0) {
                $percentage = 100;
            }

            return [
                'count' => $currentCount,
                'percentage' => round($percentage, 2)
            ];
        };

        $statistics = [
            'total' => $getStats(),
            'sent' => $getStats('sent'),
            'not_sent' => $getStats('not_sent'),
            'scheduled' => $getStats('scheduled'),
        ];

        return response()->json([
            'status' => true,
            'message' => 'Notifications retrieved',
            'data' => [
                'notifications' => $notifications,
                'statistics' => $statistics
            ]
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title_ar' => 'required|string',
            'title_en' => 'required|string',
            'body_ar' => 'required|string',
            'body_en' => 'required|string',
            'target_audience' => ['required', Rule::in(Notification::TARGET_AUDIENCES)],
            'type' => ['required', Rule::in(Notification::TYPES)],
            'status' => ['nullable', Rule::in(Notification::STATUSES)],
            'scheduled_at' => 'nullable|date',
        ]);

        $notification = Notification::create($request->all());
        return response()->json(['status' => true, 'message' => 'Notification created', 'data' => $notification]);
    }

    public function show($id)
    {
        $notification = Notification::find($id);
        if (!$notification) return response()->json(['status' => false, 'message' => 'Not found'], 404);
        return response()->json(['status' => true, 'message' => 'Notification retrieved', 'data' => $notification]);
    }

    public function update(Request $request, $id)
    {
        $notification = Notification::find($id);
        if (!$notification) return response()->json(['status' => false, 'message' => 'Not found'], 404);

        $request->validate([
            'title_ar' => 'sometimes|string',
            'title_en' => 'sometimes|string',
            'body_ar' => 'sometimes|string',
            'body_en' => 'sometimes|string',
            'target_audience' => ['sometimes', Rule::in(Notification::TARGET_AUDIENCES)],
            'type' => ['sometimes', Rule::in(Notification::TYPES)],
            'status' => ['sometimes', Rule::in(Notification::STATUSES)],
            'scheduled_at' => 'nullable|date',
        ]);

        $notification->update($request->all());
        return response()->json(['status' => true, 'message' => 'Notification updated', 'data' => $notification]);
    }

    public function destroy($id)
    {
        $notification = Notification::find($id);
        if (!$notification) return response()->json(['status' => false, 'message' => 'Not found'], 404);
        $notification->delete();
        return response()->json(['status' => true, 'message' => 'Notification deleted']);
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:notifications,id',
        ]);

        Notification::whereIn('id', $request->ids)->delete();

        return response()->json(['status' => true, 'message' => 'Notifications deleted successfully']);
    }
}
