<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\District;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DistrictController extends Controller
{
    public function index(Request $request)
    {
        $query = District::with('city');
        if($request->has('city_id')){
            $query->where('city_id', $request->city_id);
        }
        $districts = $query->orderBy('id', 'desc')->get();
        return response()->json(['status' => true, 'message' => 'Districts retrieved successfully', 'data' => $districts]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name_ar' => 'required|string',
            'name_en' => 'required|string',
            'city_id' => 'required|exists:cities,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first(), 'errors' => $validator->errors()], 422);
        }

        $district = District::create($request->all());

        return response()->json(['status' => true, 'message' => 'District created successfully', 'data' => $district]);
    }

    public function show($id)
    {
        $district = District::with('city')->find($id);

        if (!$district) {
            return response()->json(['status' => false, 'message' => 'District not found'], 404);
        }

        return response()->json(['status' => true, 'message' => 'District retrieved successfully', 'data' => $district]);
    }

    public function update(Request $request, $id)
    {
        $district = District::find($id);

        if (!$district) {
            return response()->json(['status' => false, 'message' => 'District not found'], 404);
        }

        $district->update($request->all());

        return response()->json(['status' => true, 'message' => 'District updated successfully', 'data' => $district]);
    }

    public function destroy($id)
    {
        $district = District::find($id);

        if (!$district) {
            return response()->json(['status' => false, 'message' => 'District not found'], 404);
        }

        $district->delete();

        return response()->json(['status' => true, 'message' => 'District deleted successfully']);
    }
}
