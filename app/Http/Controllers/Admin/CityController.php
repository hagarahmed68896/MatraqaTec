<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class CityController extends Controller
{
    public function index(Request $request)
    {
        $query = City::with('districts')->withCount(['companies', 'users', 'orders', 'services']);

        if ($request->has('search') && $request->search) {
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

        $items = $query->orderBy('id', 'desc')->paginate(10);

        return view('admin.cities.index', compact('items'));
    }

    public function create()
    {
        return view('admin.cities.create');
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
            return back()->withErrors($validator)->withInput();
        }

        DB::transaction(function () use ($request) {
            $city = City::create($request->only(['name_ar', 'name_en']));

            if ($request->has('districts') && is_array($request->districts)) {
                $city->districts()->createMany($request->districts);
            }
        });

        return redirect()->route('admin.cities.index')->with('success', __('City created successfully.'));
    }

    public function show($id)
    {
        $item = City::with('districts')->withCount(['companies', 'users', 'orders', 'services'])->findOrFail($id);
        return view('admin.cities.show', compact('item'));
    }

    public function edit($id)
    {
        $item = City::with('districts')->findOrFail($id);
        return view('admin.cities.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $city = City::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name_ar' => 'required|string',
            'name_en' => 'required|string',
            'districts' => 'nullable|array',
            'districts.*.id' => 'nullable|exists:districts,id',
            'districts.*.name_ar' => 'required_with:districts|string',
            'districts.*.name_en' => 'required_with:districts|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        DB::transaction(function () use ($request, $city) {
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
            } else {
                 // If no districts sent, maybe delete all? checking context usually 'districts' array sent empty if clearing
                 // BUT if key missing, might be partial update. Assuming complete form submission here.
                 // Safety: only delete if 'districts' key exists but is empty? 
                 // The validator allows nullable, so if request has 'districts' key it runs logic.
            }
        });

        return redirect()->route('admin.cities.index')->with('success', __('City updated successfully.'));
    }

    public function destroy($id)
    {
        $city = City::findOrFail($id);
        $city->delete();
        return redirect()->route('admin.cities.index')->with('success', __('City deleted successfully.'));
    }
}
