<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index()
    {
        $reviews = Review::with(['user', 'technician', 'service'])->orderBy('created_at', 'desc')->paginate(20);
        return response()->json(['status' => true, 'message' => 'Reviews retrieved', 'data' => $reviews]);
    }

    public function show($id)
    {
        $review = Review::find($id);
        if (!$review) return response()->json(['status' => false, 'message' => 'Not found'], 404);
        return response()->json(['status' => true, 'message' => 'Review retrieved', 'data' => $review]);
    }

    public function destroy($id)
    {
        $review = Review::find($id);
        if (!$review) return response()->json(['status' => false, 'message' => 'Not found'], 404);
        $review->delete();
        return response()->json(['status' => true, 'message' => 'Review deleted']);
    }
}
