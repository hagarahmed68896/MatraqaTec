<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class InventoryController extends Controller
{
    public function index()
    {
        $inventory = Inventory::all();
        return response()->json(['status' => true, 'message' => 'Inventory retrieved successfully', 'data' => $inventory]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name_ar' => 'required|string',
            'name_en' => 'required|string',
            'price' => 'required|numeric',
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
