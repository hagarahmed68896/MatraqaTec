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
    
    public function show($id)
    {
        $notification = Notification::where('user_id', auth()->id())->where('id', $id)->first();
        if (!$notification) return response()->json(['status' => false, 'message' => 'Not found'], 404);
        return response()->json(['status' => true, 'message' => 'Notification retrieved', 'data' => $notification]);
    }
    
    // Store/Destroy removed.
}
