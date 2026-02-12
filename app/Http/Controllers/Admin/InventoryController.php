<?php

namespace App\Http\Controllers\Admin;

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

        // Default sorting
        $query->orderBy('created_at', 'desc');

        // Paginate
        $items = $query->paginate($request->per_page ?? 10);

        return view('admin.inventories.index', compact('items'));
    }

    public function create()
    {
        return view('admin.inventories.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name_ar' => 'required|string',
            'name_en' => 'required|string',
            'price' => 'required|numeric',
            'status'  => 'required|in:available,not_available',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        Inventory::create($request->all());

        return redirect()->route('admin.inventories.index')->with('success', __('Item created successfully.'));
    }

    public function show($id)
    {
        $item = Inventory::findOrFail($id);
        return view('admin.inventories.show', compact('item'));
    }

    public function edit($id)
    {
        $item = Inventory::findOrFail($id);
        return view('admin.inventories.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = Inventory::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name_ar' => 'sometimes|string|max:255',
            'name_en' => 'sometimes|string|max:255',
            'price'   => 'sometimes|numeric|min:0',
            'status'  => 'sometimes|in:available,not_available',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $item->update($request->all());

        return redirect()->route('admin.inventories.index')->with('success', __('Item updated successfully.'));
    }

    public function destroy($id)
    {
        $item = Inventory::findOrFail($id);
        $item->delete();
        return redirect()->route('admin.inventories.index')->with('success', __('Item deleted successfully.'));
    }
}
