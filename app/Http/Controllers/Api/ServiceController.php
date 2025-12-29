<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ServiceController extends Controller
{
    public function index()
    {
        $services = Service::with('children')->whereNull('parent_id')->get();
        return response()->json(['status' => true, 'message' => 'Services retrieved successfully', 'data' => $services]);
    }

// Methods removed. Read-only controller.
}
