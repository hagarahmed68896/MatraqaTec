<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ChatController extends Controller
{
    /**
     * Get chat history with a specific user.
     */
    public function index(Request $request, $receiver_id)
    {
        $user = $request->user();
        
        $messages = Message::where(function($query) use ($user, $receiver_id) {
            $query->where('sender_id', $user->id)->where('receiver_id', $receiver_id);
        })->orWhere(function($query) use ($user, $receiver_id) {
            $query->where('sender_id', $receiver_id)->where('receiver_id', $user->id);
        })
        ->orderBy('created_at', 'asc')
        ->get();

        // Mark messages as read
        Message::where('sender_id', $receiver_id)
            ->where('receiver_id', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json([
            'status' => true,
            'message' => 'Chat history retrieved',
            'data' => $messages,
        ]);
    }

    /**
     * Send a message to another user.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'receiver_id' => 'required|exists:users,id',
            'content' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }

        $user = $request->user();
        $message = Message::create([
            'sender_id' => $user->id,
            'receiver_id' => $request->receiver_id,
            'content' => $request->content,
        ]);

        // In a real app, you'd trigger a WebSocket event or push notification here.

        return response()->json([
            'status' => true,
            'message' => 'Message sent successfully',
            'data' => $message,
        ]);
    }

    /**
     * Get list of conversations (latest message per user).
     */
    public function conversations(Request $request)
    {
        $user = $request->user();
        
        $latestMessages = Message::where('sender_id', $user->id)
            ->orWhere('receiver_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->unique(function ($item) use ($user) {
                return $item->sender_id == $user->id ? $item->receiver_id : $item->sender_id;
            });

        return response()->json([
            'status' => true,
            'message' => 'Conversations retrieved',
            'data' => $latestMessages->values(),
        ]);
    }
}
