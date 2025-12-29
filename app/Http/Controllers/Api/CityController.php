<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CityController extends Controller
{
    public function index()
    {
        $cities = City::with('districts')->get();
        return response()->json(['status' => true, 'message' => 'Cities retrieved successfully', 'data' => $cities]);
    }

// Methods removed. Read-only controller.
}
