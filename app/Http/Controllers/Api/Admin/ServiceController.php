<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ServiceController extends Controller
{
  public function index(Request $request)
{
    // 1. Use paginate() instead of get(). Default to 10 items per page.
    $services = Service::withCount(['technicians', 'children'])
        ->orderBy('id', 'desc')
        ->paginate($request->per_page ?? 9); 

    // 2. When using paginate(), the data is inside a "Collection". 
    // We use getCollection() to loop through and add the companies_count.
    $services->getCollection()->transform(function ($service) {
        $service->companies_count = $service->technicians()
            ->distinct('maintenance_company_id')
            ->count('maintenance_company_id');
        return $service;
    });
    
    return response()->json([
        'status' => true, 
        'message' => 'Services retrieved', 
        'data' => $services
    ]);
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
            'icon' => 'nullable|image', // Service Symbol/Code
            'children' => 'nullable|array', // Sub-services
            'children.*.name_ar' => 'required_with:children|string',
            'children.*.name_en' => 'required_with:children|string',
            'children.*.image' => 'nullable|image',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first(), 'errors' => $validator->errors()], 422);
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
                // Because file uploads in arrays come separately in Request if not JSON
                // If sent as FormData: children[0][name_ar], children[0][image]
                
                $childPayload = [
                    'name_ar' => $childData['name_ar'],
                    'name_en' => $childData['name_en'],
                    'parent_id' => $service->id,
                ];
                
                if ($request->hasFile("children.$index.image")) {
                    $childPayload['image'] = $request->file("children.$index.image")->store('services', 'public');
                }
                
                Service::create($childPayload);
            }
        }

        return response()->json(['status' => true, 'message' => 'Service created successfully', 'data' => $service->load('children')]);
    }

    public function show($id)
    {
        $service = Service::withCount(['technicians', 'children'])->find($id);
        if (!$service) return response()->json(['status' => false, 'message' => 'Service not found'], 404);
        
        $service->companies_count = $service->technicians()->distinct('maintenance_company_id')->count('maintenance_company_id');
        
        return response()->json(['status' => true, 'message' => 'Service retrieved', 'data' => $service]);
    }

    public function update(Request $request, $id)
    {
        $service = Service::find($id);
        if (!$service) return response()->json(['status' => false, 'message' => 'Service not found'], 404);

        $validator = Validator::make($request->all(), [
            'name_ar' => 'sometimes|string',
            'name_en' => 'sometimes|string',
            'description_ar' => 'nullable|string',
            'description_en' => 'nullable|string',
            'price' => 'nullable|numeric',
            'image' => 'nullable|image',
            'icon' => 'nullable|image',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first(), 'errors' => $validator->errors()], 422);
        }

        $data = $request->except(['image', 'icon', 'children']);
        
        if ($request->hasFile('image')) {
            // if ($service->image) Storage::disk('public')->delete($service->image);
            $data['image'] = $request->file('image')->store('services', 'public');
        }
        
        if ($request->hasFile('icon')) {
             // if ($service->icon) Storage::disk('public')->delete($service->icon);
            $data['icon'] = $request->file('icon')->store('services/icons', 'public');
        }

        $service->update($data);    

        return response()->json(['status' => true, 'message' => 'Service updated successfully', 'data' => $service]);
    }

    public function destroy($id)
    {
        $service = Service::find($id);
        if (!$service) return response()->json(['status' => false, 'message' => 'Service not found'], 404);
        $service->delete();
        return response()->json(['status' => true, 'message' => 'Service deleted successfully']);
    }
}
