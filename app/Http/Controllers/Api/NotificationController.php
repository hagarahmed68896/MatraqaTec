<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $audienceType = 'all';
        
        // Map user type to target_audience
        switch ($user->type) {
            case 'individual':
            case 'corporate_customer':
                $audienceType = 'clients';
                break;
            case 'technician':
                $audienceType = 'technicians';
                break;
            case 'maintenance_company':
                $audienceType = 'companies';
                break;
        }

        $notifications = Notification::where(function ($query) use ($user, $audienceType) {
                $query->where('user_id', $user->id)
                      ->orWhereIn('target_audience', [$audienceType, 'all']);
            })
            ->where('status', 'sent')
            ->whereDoesntHave('userActions', function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->where('is_deleted', true);
            })
            ->with(['userActions' => function ($q) use ($user) {
                $q->where('user_id', $user->id);
            }])
            ->latest()
            ->paginate($request->get('limit', 10));

        // Format is_read based on user actions for broadcast notifications
        $notifications->getCollection()->transform(function ($notification) {
            if (!$notification->user_id) { // Broadcast notification
                $action = $notification->userActions->first();
                $notification->is_read = $action ? $action->is_read : false;
            }
            unset($notification->userActions);
            return $notification;
        });

        return response()->json(['status' => true, 'message' => 'Notifications retrieved', 'data' => $notifications]);
    }
    
    public function markAsRead($id)
    {
        $user = auth()->user();
        $audienceType = 'all';
        switch ($user->type) {
            case 'individual':
            case 'corporate_customer': $audienceType = 'clients'; break;
            case 'technician': $audienceType = 'technicians'; break;
            case 'maintenance_company': $audienceType = 'companies'; break;
        }

        $notification = Notification::where(function ($query) use ($user, $audienceType) {
                $query->where('user_id', $user->id)
                      ->orWhereIn('target_audience', [$audienceType, 'all']);
            })->where('id', $id)->first();

        if (!$notification) return response()->json(['status' => false, 'message' => 'Not found'], 404);
        
        if ($notification->user_id == $user->id) {
            $notification->update(['is_read' => true]);
        } else {
            \App\Models\UserNotificationAction::updateOrCreate(
                ['user_id' => $user->id, 'notification_id' => $notification->id],
                ['is_read' => true]
            );
        }

        return response()->json(['status' => true, 'message' => 'Notification marked as read']);
    }

    public function markAllAsRead()
    {
        $user = auth()->user();
        $audienceType = 'all';
        switch ($user->type) {
            case 'individual':
            case 'corporate_customer': $audienceType = 'clients'; break;
            case 'technician': $audienceType = 'technicians'; break;
            case 'maintenance_company': $audienceType = 'companies'; break;
        }

        // Mark individual ones
        Notification::where('user_id', $user->id)->unread()->update(['is_read' => true]);

        // Mark broadcast ones
        $broadcastNotifications = Notification::whereIn('target_audience', [$audienceType, 'all'])
            ->where('status', 'sent')
            ->pluck('id');

        foreach ($broadcastNotifications as $id) {
            \App\Models\UserNotificationAction::updateOrCreate(
                ['user_id' => $user->id, 'notification_id' => $id],
                ['is_read' => true]
            );
        }

        return response()->json(['status' => true, 'message' => 'All notifications marked as read']);
    }

    public function destroy($id)
    {
        $user = auth()->user();
        
        $audienceType = 'all';
        switch ($user->type) {
            case 'individual':
            case 'corporate_customer':
                $audienceType = 'clients';
                break;
            case 'technician':
                $audienceType = 'technicians';
                break;
            case 'maintenance_company':
                $audienceType = 'companies';
                break;
        }

        $notification = Notification::where(function ($query) use ($user, $audienceType) {
                $query->where('user_id', $user->id)
                      ->orWhereIn('target_audience', [$audienceType, 'all']);
            })
            ->where('id', $id)
            ->first();

        if (!$notification) {
            return response()->json(['status' => false, 'message' => 'Not found'], 404);
        }
        
        // Only actually delete if it specifically belongs to this user
        if ($notification->user_id == $user->id) {
            $notification->delete();
        } else {
            \App\Models\UserNotificationAction::updateOrCreate(
                ['user_id' => $user->id, 'notification_id' => $notification->id],
                ['is_deleted' => true]
            );
        }
        
        return response()->json(['status' => true, 'message' => 'Notification deleted']);
    }

    public function destroyAll()
    {
        $user = auth()->user();
        
        $audienceType = 'all';
        switch ($user->type) {
            case 'individual':
            case 'corporate_customer': $audienceType = 'clients'; break;
            case 'technician': $audienceType = 'technicians'; break;
            case 'maintenance_company': $audienceType = 'companies'; break;
        }

        Notification::where('user_id', $user->id)->delete();

        // Mark broadcast ones as deleted
        $broadcastNotifications = Notification::whereIn('target_audience', [$audienceType, 'all'])
            ->where('status', 'sent')
            ->pluck('id');

        foreach ($broadcastNotifications as $id) {
            \App\Models\UserNotificationAction::updateOrCreate(
                ['user_id' => $user->id, 'notification_id' => $id],
                ['is_deleted' => true]
            );
        }

        return response()->json(['status' => true, 'message' => 'All notifications deleted']);
    }
}
