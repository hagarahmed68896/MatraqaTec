<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\TechnicianRequest;
use Illuminate\Http\Request;

class TechnicianRequestController extends Controller
{
    public function index()
    {
        $requests = TechnicianRequest::orderBy('created_at', 'desc')->get();
        return response()->json(['status' => true, 'message' => 'Requests retrieved', 'data' => $requests]);
    }

    public function show($id)
    {
        $techRequest = TechnicianRequest::find($id);
        if (!$techRequest) return response()->json(['status' => false, 'message' => 'Not found'], 404);
        return response()->json(['status' => true, 'message' => 'Request retrieved', 'data' => $techRequest]);
    }

    public function update(Request $request, $id)
    {
        $techRequest = TechnicianRequest::find($id);
        if (!$techRequest) return response()->json(['status' => false, 'message' => 'Not found'], 404);
        
        $techRequest->update($request->all());

        return response()->json(['status' => true, 'message' => 'Request updated', 'data' => $techRequest]);
    }

    public function destroy($id)
    {
        $techRequest = TechnicianRequest::find($id);
        if (!$techRequest) return response()->json(['status' => false, 'message' => 'Not found'], 404);
        $techRequest->delete();
        return response()->json(['status' => true, 'message' => 'Request deleted']);
    }
}
