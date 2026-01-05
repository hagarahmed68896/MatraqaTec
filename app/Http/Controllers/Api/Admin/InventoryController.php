<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class InventoryController extends Controller
{
public function index(Request $request)
{
    $query = Inventory::query();

    // Search by name_ar or name_en
    if ($request->has('search')) {
        $search = $request->search;
        $query->where(function ($q) use ($search) {
            $q->where('name_ar', 'like', "%{$search}%")
              ->orWhere('name_en', 'like', "%{$search}%");
        });
    }

    // Default sorting (Optional but recommended: newest first)
    $query->orderBy('created_at', 'desc');

    // Paginate with 9 items per page
    $inventory = $query->paginate($request->per_page ?? 9);

    return response()->json([
        'status' => true, 
        'message' => 'Inventory retrieved successfully', 
        'data' => $inventory
    ]);
}

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name_ar' => 'required|string',
            'name_en' => 'required|string',
            'price' => 'required|numeric',
            'status'  => 'required|in:available,not_available', // Strict status check
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first(), 'errors' => $validator->errors()], 422);
        }

        $item = Inventory::create($request->all());

        return response()->json(['status' => true, 'message' => 'Item created successfully', 'data' => $item]);
    }

    public function show($id)
    {
        $item = Inventory::find($id);

        if (!$item) {
            return response()->json(['status' => false, 'message' => 'Item not found'], 404);
        }

        return response()->json(['status' => true, 'message' => 'Item retrieved successfully', 'data' => $item]);
    }

  public function update(Request $request, $id)
    {
        $item = Inventory::find($id);

        if (!$item) {
            return response()->json(['status' => false, 'message' => 'Item not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name_ar' => 'sometimes|string|max:255',
            'name_en' => 'sometimes|string|max:255',
            'price'   => 'sometimes|numeric|min:0',
            'status'  => 'sometimes|in:available,not_available', // Optional update
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first(), 'errors' => $validator->errors()], 422);
        }

        $item->update($request->all());

        return response()->json(['status' => true, 'message' => 'Item updated successfully', 'data' => $item]);
    }

    public function destroy($id)
    {
        $item = Inventory::find($id);

        if (!$item) {
            return response()->json(['status' => false, 'message' => 'Item not found'], 404);
        }

        $item->delete();

        return response()->json(['status' => true, 'message' => 'Item deleted successfully']);
    }
}
