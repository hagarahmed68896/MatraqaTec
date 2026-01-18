<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PrivacyPolicy;
use Illuminate\Http\Request;

class PrivacyPolicyController extends Controller
{
    public function index()
    {
        $policies = PrivacyPolicy::where('status', 'active')->get();
        return response()->json(['status' => true, 'message' => 'Privacy Policies retrieved successfully', 'data' => $policies]);
    }

    public function show($id)
    {
        $policy = PrivacyPolicy::find($id);
        if (!$policy || $policy->status !== 'active') {
            return response()->json(['status' => false, 'message' => 'Privacy Policy not found'], 404);
        }
        return response()->json(['status' => true, 'message' => 'Privacy Policy retrieved', 'data' => $policy]);
    }
}
