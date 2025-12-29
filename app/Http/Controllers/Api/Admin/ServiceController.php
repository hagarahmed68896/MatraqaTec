<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ServiceController extends Controller
{
    public function index()
    {
        $services = Service::orderBy('id', 'desc')->get();
        return response()->json(['status' => true, 'message' => 'Services retrieved', 'data' => $services]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name_ar' => 'required|string',
            'name_en' => 'required|string',
            'description_ar' => 'nullable|string',
            'description_en' => 'nullable|string',
            'image' => 'nullable|image',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first(), 'errors' => $validator->errors()], 422);
        }

        $data = $request->except('image');
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('services', 'public');
        }

        $service = Service::create($data);

        return response()->json(['status' => true, 'message' => 'Service created successfully', 'data' => $service]);
    }

    public function show($id)
    {
        $service = Service::find($id);
        if (!$service) return response()->json(['status' => false, 'message' => 'Service not found'], 404);
        return response()->json(['status' => true, 'message' => 'Service retrieved', 'data' => $service]);
    }

    public function update(Request $request, $id)
    {
        $service = Service::find($id);
        if (!$service) return response()->json(['status' => false, 'message' => 'Service not found'], 404);

        $data = $request->except('image');
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('services', 'public');
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
