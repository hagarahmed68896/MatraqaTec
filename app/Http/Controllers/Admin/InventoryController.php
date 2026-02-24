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

        // Status Filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Default sorting
        $query->orderBy('created_at', 'desc');

        // Paginate
        $items = $query->paginate($request->per_page ?? 12);

        return view('admin.inventory.index', compact('items'));
    }

    public function create()
    {
        return view('admin.inventory.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'quantity' => 'nullable|integer|min:0',
            'status'  => 'required|in:available,not_available',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $data = $request->except('image');

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/inventory'), $filename);
            $data['image'] = 'uploads/inventory/' . $filename;
        }

        Inventory::create($data);

        return redirect()->route('admin.inventory.index')->with('success', __('Item created successfully.'));
    }

    public function show($id)
    {
        $item = Inventory::findOrFail($id);
        return view('admin.inventory.show', compact('item'));
    }

    public function edit($id)
    {
        $item = Inventory::findOrFail($id);
        return view('admin.inventory.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = Inventory::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name_ar' => 'sometimes|string|max:255',
            'name_en' => 'sometimes|string|max:255',
            'price'   => 'sometimes|numeric|min:0',
            'quantity' => 'nullable|integer|min:0',
            'status'  => 'sometimes|in:available,not_available',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $data = $request->except('image');

        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($item->image && file_exists(public_path($item->image))) {
                @unlink(public_path($item->image));
            }

            $file = $request->file('image');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/inventory'), $filename);
            $data['image'] = 'uploads/inventory/' . $filename;
        }

        $item->update($data);

        return redirect()->route('admin.inventory.index')->with('success', __('Item updated successfully.'));
    }

    public function destroy($id)
    {
        $item = Inventory::findOrFail($id);
        
        if ($item->image && file_exists(public_path($item->image))) {
            @unlink(public_path($item->image));
        }

        $item->delete();
        return redirect()->route('admin.inventory.index')->with('success', __('Item deleted successfully.'));
    }
}
