<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class InventoryController extends Controller
{
    /**
     * List inventory items for the authenticated maintenance company
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        if (!$user->maintenanceCompany) {
            return response()->json(['status' => false, 'message' => 'User is not a company'], 403);
        }

        $query = Inventory::where('maintenance_company_id', $user->maintenanceCompany->id);

        // Search by name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name_ar', 'like', "%{$search}%")
                  ->orWhere('name_en', 'like', "%{$search}%");
            });
        }

        $items = $query->latest()->paginate($request->input('per_page', 15));

        return response()->json([
            'status' => true,
            'message' => 'Inventory retrieved successfully',
            'data' => $items
        ]);
    }

    /**
     * Store a new inventory item
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        $company = $user->maintenanceCompany;
        if (!$company) {
            return response()->json(['status' => false, 'message' => 'User is not a company'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:0',
            'status' => 'required|in:available,unavailable',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120', // Max 5MB as per screenshot "5 ميجابايت"
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }

        $data = $request->all();
        $data['maintenance_company_id'] = $company->id;

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('inventory', 'public');
            $data['image'] = 'storage/' . $path;
        }

        $item = Inventory::create($data);

        return response()->json([
            'status' => true,
            'message' => 'Item added successfully',
            'data' => $item
        ]);
    }

    /**
     * Update an existing inventory item
     */
    public function update(Request $request, $id)
    {
        $user = auth()->user();
        $company = $user->maintenanceCompany;
        if (!$company) {
            return response()->json(['status' => false, 'message' => 'User is not a company'], 403);
        }

        $item = Inventory::where('maintenance_company_id', $company->id)->find($id);
        if (!$item) {
            return response()->json(['status' => false, 'message' => 'Item not found or unauthorized'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name_ar' => 'sometimes|string|max:255',
            'name_en' => 'sometimes|string|max:255',
            'price' => 'sometimes|numeric|min:0',
            'quantity' => 'sometimes|integer|min:0',
            'status' => 'sometimes|in:available,unavailable',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }

        $data = $request->all();

        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($item->image) {
                $oldPath = str_replace('storage/', '', $item->image);
                Storage::disk('public')->delete($oldPath);
            }
            $path = $request->file('image')->store('inventory', 'public');
            $data['image'] = 'storage/' . $path;
        }

        $item->update($data);

        return response()->json([
            'status' => true,
            'message' => 'Item updated successfully',
            'data' => $item
        ]);
    }

    /**
     * Delete an inventory item
     */
    public function destroy($id)
    {
        $user = auth()->user();
        $company = $user->maintenanceCompany;
        if (!$company) {
            return response()->json(['status' => false, 'message' => 'User is not a company'], 403);
        }

        $item = Inventory::where('maintenance_company_id', $company->id)->find($id);
        if (!$item) {
            return response()->json(['status' => false, 'message' => 'Item not found or unauthorized'], 404);
        }

        // Delete image
        if ($item->image) {
            $path = str_replace('storage/', '', $item->image);
            Storage::disk('public')->delete($path);
        }

        $item->delete();

        return response()->json([
            'status' => true,
            'message' => 'Item deleted successfully'
        ]);
    }
}
