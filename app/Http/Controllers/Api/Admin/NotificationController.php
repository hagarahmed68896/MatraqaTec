<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = Notification::latest()->get();
        return response()->json(['status' => true, 'message' => 'Notifications retrieved', 'data' => $notifications]);
    }

    public function store(Request $request)
    {
        $notification = Notification::create($request->all());
        return response()->json(['status' => true, 'message' => 'Notification created', 'data' => $notification]);
    }

    public function show($id)
    {
        $notification = Notification::find($id);
        if (!$notification) return response()->json(['status' => false, 'message' => 'Not found'], 404);
        return response()->json(['status' => true, 'message' => 'Notification retrieved', 'data' => $notification]);
    }

    public function destroy($id)
    {
        $notification = Notification::find($id);
        if (!$notification) return response()->json(['status' => false, 'message' => 'Not found'], 404);
        $notification->delete();
        return response()->json(['status' => true, 'message' => 'Notification deleted']);
    }
}
