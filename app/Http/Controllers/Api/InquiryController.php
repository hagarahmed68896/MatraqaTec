<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Inquiry;
use Illuminate\Http\Request;

class InquiryController extends Controller
{
    public function index()
    {
        $inquiries = Inquiry::with('user')
            ->where('user_id', auth()->id())
            ->get();
        return response()->json(['status' => true, 'message' => 'Inquiries retrieved', 'data' => $inquiries]);
    }

    public function store(Request $request)
    {
        $request->merge(['user_id' => auth()->id()]);
        $inquiry = Inquiry::create($request->all());
        return response()->json(['status' => true, 'message' => 'Inquiry submitted', 'data' => $inquiry]);
    }

    public function show($id)
    {
        $inquiry = Inquiry::where('user_id', auth()->id())->where('id', $id)->first();
        if (!$inquiry) return response()->json(['status' => false, 'message' => 'Not found'], 404);
        return response()->json(['status' => true, 'message' => 'Inquiry retrieved', 'data' => $inquiry]);
    }
    
}
