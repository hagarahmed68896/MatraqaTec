<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Service::withCount(['technicians', 'children']);

        if ($request->has('search') && $request->search) {
            $query->where('name_ar', 'like', '%' . $request->search . '%')
                  ->orWhere('name_en', 'like', '%' . $request->search . '%');
        }

        $items = $query->orderBy('id', 'desc')->paginate(10);
        
        // Add companies count logic
        $items->getCollection()->transform(function ($service) {
            $service->companies_count = $service->technicians()
                ->distinct('maintenance_company_id')
                ->count('maintenance_company_id');
            return $service;
        });

        return view('admin.services.index', compact('items'));
    }

    public function create()
    {
        return view('admin.services.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name_ar' => 'required|string',
            'name_en' => 'required|string',
            'description_ar' => 'nullable|string',
            'description_en' => 'nullable|string',
            'price' => 'nullable|numeric',
            'image' => 'nullable|image',
            'icon' => 'nullable|image',
            'children' => 'nullable|array',
            'children.*.name_ar' => 'required_with:children|string',
            'children.*.name_en' => 'required_with:children|string',
            'children.*.image' => 'nullable|image',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $data = $request->except(['image', 'icon', 'children']);
        
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('services', 'public');
        }
        
        if ($request->hasFile('icon')) {
            $data['icon'] = $request->file('icon')->store('services/icons', 'public');
        }

        $service = Service::create($data);
        
        // Handle Sub-services
        if ($request->has('children') && is_array($request->children)) {
            foreach ($request->children as $index => $childData) {
                // If sub-service image is uploaded
                $childImage = null;
                if ($request->hasFile("children.$index.image")) {
                    $childImage = $request->file("children.$index.image")->store('services', 'public');
                }

                Service::create([
                    'name_ar' => $childData['name_ar'],
                    'name_en' => $childData['name_en'],
                    'parent_id' => $service->id,
                    'image' => $childImage,
                ]);
            }
        }

        return redirect()->route('admin.services.index')->with('success', __('Service created successfully.'));
    }

    public function show($id)
    {
        $item = Service::withCount(['technicians', 'children'])->with('children')->findOrFail($id);
        $item->companies_count = $item->technicians()->distinct('maintenance_company_id')->count('maintenance_company_id');
        return view('admin.services.show', compact('item'));
    }

    public function edit($id)
    {
        $item = Service::with('children')->findOrFail($id);
        return view('admin.services.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $service = Service::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name_ar' => 'required|string',
            'name_en' => 'required|string',
            'description_ar' => 'nullable|string',
            'description_en' => 'nullable|string',
            'price' => 'nullable|numeric',
            'image' => 'nullable|image',
            'icon' => 'nullable|image',
            'children' => 'nullable|array',
            'children.*.id' => 'nullable|exists:services,id',
            'children.*.name_ar' => 'required_with:children|string',
            'children.*.name_en' => 'required_with:children|string',
            'children.*.image' => 'nullable|image',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $data = $request->except(['image', 'icon', 'children']);
        
        if ($request->hasFile('image')) {
            if ($service->image) {
                Storage::disk('public')->delete($service->image);
            }
            $data['image'] = $request->file('image')->store('services', 'public');
        }
        
        if ($request->hasFile('icon')) {
            if ($service->icon) {
                Storage::disk('public')->delete($service->icon);
            }
            $data['icon'] = $request->file('icon')->store('services/icons', 'public');
        }

        $service->update($data);

        // Handle Sub-services Sync
        if ($request->has('children')) {
            $submittedIds = collect($request->children)->pluck('id')->filter()->toArray();
            
            // Delete sub-services that are no longer in the list
            $service->children()->whereNotIn('id', $submittedIds)->each(function($child) {
                if ($child->image) {
                    Storage::disk('public')->delete($child->image);
                }
                $child->delete();
            });

            foreach ($request->children as $index => $childData) {
                $childImage = null;
                if ($request->hasFile("children.$index.image")) {
                    $childImage = $request->file("children.$index.image")->store('services', 'public');
                }

                if (isset($childData['id']) && $childData['id']) {
                    // Update existing
                    $child = Service::find($childData['id']);
                    $childUpdateData = [
                        'name_ar' => $childData['name_ar'],
                        'name_en' => $childData['name_en'],
                    ];
                    if ($childImage) {
                        if ($child->image) {
                            Storage::disk('public')->delete($child->image);
                        }
                        $childUpdateData['image'] = $childImage;
                    }
                    $child->update($childUpdateData);
                } else {
                    // Create new
                    Service::create([
                        'name_ar' => $childData['name_ar'],
                        'name_en' => $childData['name_en'],
                        'parent_id' => $service->id,
                        'image' => $childImage,
                    ]);
                }
            }
        }

        return redirect()->route('admin.services.index')->with('success', __('Service updated successfully.'));
    }

    public function destroy($id)
    {
        $item = Service::findOrFail($id);
        $item->delete();
        return redirect()->route('admin.services.index')->with('success', __('Service deleted successfully.'));
    }
}
