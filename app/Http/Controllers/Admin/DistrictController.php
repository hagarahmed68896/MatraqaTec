<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\District;
use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DistrictController extends Controller
{
    public function index(Request $request)
    {
        $query = District::with('city');
        
        if($request->has('city_id') && $request->city_id){
            $query->where('city_id', $request->city_id);
        }

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name_ar', 'like', "%{$search}%")
                  ->orWhere('name_en', 'like', "%{$search}%");
            });
        }

        $items = $query->orderBy('id', 'desc')->paginate(15);
        
        return view('admin.districts.index', compact('items'));
    }

    public function create()
    {
        $cities = City::all();
        return view('admin.districts.create', compact('cities'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name_ar' => 'required|string',
            'name_en' => 'required|string',
            'city_id' => 'required|exists:cities,id',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        District::create($request->all());

        return redirect()->route('admin.districts.index')->with('success', __('District created successfully.'));
    }

    public function show($id)
    {
        $item = District::with('city')->findOrFail($id);
        return view('admin.districts.show', compact('item'));
    }

    public function edit($id)
    {
        $item = District::findOrFail($id);
        $cities = City::all();
        return view('admin.districts.edit', compact('item', 'cities'));
    }

    public function update(Request $request, $id)
    {
        $district = District::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name_ar' => 'required|string',
            'name_en' => 'required|string',
            'city_id' => 'required|exists:cities,id',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $district->update($request->all());

        return redirect()->route('admin.districts.index')->with('success', __('District updated successfully.'));
    }

    public function destroy($id)
    {
        $district = District::findOrFail($id);
        $district->delete();
        return redirect()->route('admin.districts.index')->with('success', __('District deleted successfully.'));
    }
}
