<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = Notification::where('user_id', auth()->id())->latest()->get();
        return response()->json(['status' => true, 'message' => 'Notifications retrieved', 'data' => $notifications]);
    }
    
    public function markAsRead($id)
    {
        $notification = Notification::where('user_id', auth()->id())->where('id', $id)->first();
        if (!$notification) return response()->json(['status' => false, 'message' => 'Not found'], 404);
        
        $notification->update(['is_read' => true]);
        return response()->json(['status' => true, 'message' => 'Notification marked as read']);
    }

    public function markAllAsRead()
    {
        Notification::where('user_id', auth()->id())->unread()->update(['is_read' => true]);
        return response()->json(['status' => true, 'message' => 'All notifications marked as read']);
    }

    public function destroy($id)
    {
        $notification = Notification::where('user_id', auth()->id())->where('id', $id)->first();
        if (!$notification) return response()->json(['status' => false, 'message' => 'Not found'], 404);
        
        $notification->delete();
        return response()->json(['status' => true, 'message' => 'Notification deleted']);
    }

    public function destroyAll()
    {
        Notification::where('user_id', auth()->id())->delete();
        return response()->json(['status' => true, 'message' => 'All notifications deleted']);
    }
}
