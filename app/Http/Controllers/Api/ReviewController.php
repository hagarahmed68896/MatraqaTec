<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        // Reviews are typically public or per-resource. 
        // Allowing filtering by technician/service.
        $query = Review::with(['user', 'technician', 'service']);
        if ($request->has('technician_id')) $query->where('technician_id', $request->technician_id);
        if ($request->has('service_id')) $query->where('service_id', $request->service_id);
        $reviews = $query->get();
        return response()->json(['status' => true, 'message' => 'Reviews retrieved', 'data' => $reviews]);
    }

    public function store(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $order = \App\Models\Order::find($request->order_id);

        // Create review and automatically link technician/service from order
        $review = Review::create([
            'user_id' => auth()->id(),
            'order_id' => $order->id,
            'technician_id' => $order->technician_id,
            'service_id' => $order->service_id,
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return response()->json(['status' => true, 'message' => 'Review created', 'data' => $review]);
    }

    public function show($id)
    {
        $review = Review::find($id);
        if (!$review) return response()->json(['status' => false, 'message' => 'Not found'], 404);
        return response()->json(['status' => true, 'message' => 'Review retrieved', 'data' => $review]);
    }
}
