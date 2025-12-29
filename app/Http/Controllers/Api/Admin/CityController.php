<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CityController extends Controller
{
    public function index()
    {
        $cities = City::with('districts')->orderBy('id', 'desc')->get();
        return response()->json(['status' => true, 'message' => 'Cities retrieved successfully', 'data' => $cities]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name_ar' => 'required|string',
            'name_en' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first(), 'errors' => $validator->errors()], 422);
        }

        $city = City::create($request->all());

        return response()->json(['status' => true, 'message' => 'City created successfully', 'data' => $city]);
    }

    public function show($id)
    {
        $city = City::with('districts')->find($id);

        if (!$city) {
            return response()->json(['status' => false, 'message' => 'City not found'], 404);
        }

        return response()->json(['status' => true, 'message' => 'City retrieved successfully', 'data' => $city]);
    }

    public function update(Request $request, $id)
    {
        $city = City::find($id);

        if (!$city) {
            return response()->json(['status' => false, 'message' => 'City not found'], 404);
        }

        $city->update($request->all());

        return response()->json(['status' => true, 'message' => 'City updated successfully', 'data' => $city]);
    }

    public function destroy($id)
    {
        $city = City::find($id);

        if (!$city) {
            return response()->json(['status' => false, 'message' => 'City not found'], 404);
        }

        $city->delete();

        return response()->json(['status' => true, 'message' => 'City deleted successfully']);
    }
}
