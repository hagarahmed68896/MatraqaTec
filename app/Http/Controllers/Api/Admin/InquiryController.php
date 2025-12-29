<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inquiry;
use Illuminate\Http\Request;

class InquiryController extends Controller
{
    public function index()
    {
        $inquiries = Inquiry::with('user')->get();
        return response()->json(['status' => true, 'message' => 'Inquiries retrieved', 'data' => $inquiries]);
    }

    public function store(Request $request)
    {
        $inquiry = Inquiry::create($request->all());
        return response()->json(['status' => true, 'message' => 'Inquiry submitted', 'data' => $inquiry]);
    }

    public function show($id)
    {
        $inquiry = Inquiry::find($id);
        if (!$inquiry) return response()->json(['status' => false, 'message' => 'Not found'], 404);
        return response()->json(['status' => true, 'message' => 'Inquiry retrieved', 'data' => $inquiry]);
    }

    public function update(Request $request, $id)
    {
        $inquiry = Inquiry::find($id);
        if (!$inquiry) return response()->json(['status' => false, 'message' => 'Not found'], 404);
        $inquiry->update($request->all());
        return response()->json(['status' => true, 'message' => 'Inquiry updated', 'data' => $inquiry]);
    }

    public function destroy($id)
    {
        $inquiry = Inquiry::find($id);
        if (!$inquiry) return response()->json(['status' => false, 'message' => 'Not found'], 404);
        $inquiry->delete();
        return response()->json(['status' => true, 'message' => 'Inquiry deleted']);
    }
}
