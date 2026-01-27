<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\District;
use Illuminate\Http\Request;

class DistrictController extends Controller
{
    public function index()
    {
        $districts = District::with('city')->get();
        return response()->json(['status' => true, 'message' => 'Districts retrieved successfully', 'data' => $districts]);
    }

}
