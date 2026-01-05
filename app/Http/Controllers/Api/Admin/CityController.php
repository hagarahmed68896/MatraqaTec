<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CityController extends Controller
{
    public function index(Request $request)
    {
        $query = City::with('districts')->withCount(['companies', 'users', 'orders', 'services']);

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name_ar', 'like', "%{$search}%")
                  ->orWhere('name_en', 'like', "%{$search}%")
                  ->orWhereHas('districts', function($d) use ($search) {
                      $d->where('name_ar', 'like', "%{$search}%")
                        ->orWhere('name_en', 'like', "%{$search}%");
                  });
            });
        }

        $cities = $query->orderBy('id', 'desc')->paginate(9);

        return response()->json(['status' => true, 'message' => 'Cities retrieved successfully', 'data' => $cities]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name_ar' => 'required|string',
            'name_en' => 'required|string',
            'districts' => 'nullable|array',
            'districts.*.name_ar' => 'required_with:districts|string',
            'districts.*.name_en' => 'required_with:districts|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first(), 'errors' => $validator->errors()], 422);
        }

        $city = \Illuminate\Support\Facades\DB::transaction(function () use ($request) {
            $city = City::create($request->only(['name_ar', 'name_en']));

            if ($request->has('districts') && is_array($request->districts)) {
                $city->districts()->createMany($request->districts);
            }

            return $city;
        });

        return response()->json(['status' => true, 'message' => 'City created successfully', 'data' => $city->load('districts')]);
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

        $validator = Validator::make($request->all(), [
            'name_ar' => 'required|string',
            'name_en' => 'required|string',
            'districts' => 'nullable|array',
            'districts.*.id' => 'nullable|exists:districts,id', // ID is optional for new districts
            'districts.*.name_ar' => 'required_with:districts|string',
            'districts.*.name_en' => 'required_with:districts|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first(), 'errors' => $validator->errors()], 422);
        }

        $city->update($request->only(['name_ar', 'name_en']));

        if ($request->has('districts')) {
            // Get IDs from request to identify which to keep
            $requestedIds = collect($request->districts)->pluck('id')->filter()->toArray();
            
            // Delete districts not in the request
            $city->districts()->whereNotIn('id', $requestedIds)->delete();

            foreach ($request->districts as $districtData) {
                if (isset($districtData['id'])) {
                    // Update existing
                    $city->districts()->where('id', $districtData['id'])->update([
                        'name_ar' => $districtData['name_ar'],
                        'name_en' => $districtData['name_en']
                    ]);
                } else {
                    // Create new
                    $city->districts()->create([
                        'name_ar' => $districtData['name_ar'],
                        'name_en' => $districtData['name_en']
                    ]);
                }
            }
        }

        return response()->json(['status' => true, 'message' => 'City updated successfully', 'data' => $city->load('districts')]);
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
