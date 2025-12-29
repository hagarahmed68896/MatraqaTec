<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TechnicianRequest;
use App\Models\User;
use App\Models\Technician;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TechnicianRequestController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user) return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
        
        // Assuming requests are linked to user (email or user_id context). 
        // If not, users can't see "their" requests easily without a link. 
        // Assuming user_id column exists or using email match.
        // For safety, only allow if user_id present.
        $requests = TechnicianRequest::where('user_id', $user->id)->get();
        return response()->json(['status' => true, 'message' => 'Requests retrieved', 'data' => $requests]);
    }

    public function store(Request $request)
    {
        // Public or User. If User logged in, force ID.
        if (auth()->check()) {
            $request->merge(['user_id' => auth()->id()]);
        }
        $techRequest = TechnicianRequest::create($request->all());
        return response()->json(['status' => true, 'message' => 'Request submitted successfully', 'data' => $techRequest]);
    }

    public function show($id)
    {
        $techRequest = TechnicianRequest::where('user_id', auth()->id())->where('id', $id)->first();
        if (!$techRequest) return response()->json(['status' => false, 'message' => 'Not found'], 404);
        return response()->json(['status' => true, 'message' => 'Request retrieved', 'data' => $techRequest]);
    }

    public function update(Request $request, $id)
    {
         // Users usually don't update requests once submitted, maybe cancel.
         // Restricting to Admin mostly.
         return response()->json(['status' => false, 'message' => 'Update not allowed'], 403);
    }
}
