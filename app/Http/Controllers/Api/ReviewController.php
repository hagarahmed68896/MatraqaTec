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
        // User creates review.
        $request->merge(['user_id' => auth()->id()]); // Force user_id
        $review = Review::create($request->all());
        return response()->json(['status' => true, 'message' => 'Review created', 'data' => $review]);
    }

    public function show($id)
    {
        $review = Review::find($id);
        if (!$review) return response()->json(['status' => false, 'message' => 'Not found'], 404);
        return response()->json(['status' => true, 'message' => 'Review retrieved', 'data' => $review]);
    }
}
