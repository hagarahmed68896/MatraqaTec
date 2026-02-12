<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Content;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ContentController extends Controller
{
    public function index(Request $request)
    {
        $query = Content::with('items');

        if ($request->has('search')) {
            $search = $request->search;
            $query->where('title_ar', 'like', "%{$search}%")
                  ->orWhere('title_en', 'like', "%{$search}%");
        }

        $items = $query->orderBy('id', 'desc')->paginate(10);
        return view('admin.contents.index', compact('items'));
    }

    public function create()
    {
        return view('admin.contents.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title_ar' => 'required|string',
            'title_en' => 'required|string',
            'is_visible' => 'boolean',
            'items' => 'nullable|array',
            'items.*.title_ar' => 'required|string',
            'items.*.title_en' => 'required|string',
            'items.*.description_ar' => 'nullable|string',
            'items.*.description_en' => 'nullable|string',
            'items.*.button_text_ar' => 'nullable|string',
            'items.*.button_text_en' => 'nullable|string',
            'items.*.image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        DB::transaction(function () use ($request) {
            $content = Content::create($request->only(['title_ar', 'title_en', 'is_visible']));

            if ($request->has('items')) {
                foreach ($request->items as $index => $itemData) {
                    $imagePath = null;
                    if ($request->hasFile("items.{$index}.image")) {
                        $imagePath = $request->file("items.{$index}.image")->store('content_items', 'public');
                    }

                    $content->items()->create([
                        'title_ar' => $itemData['title_ar'] ?? null,
                        'title_en' => $itemData['title_en'] ?? null,
                        'description_ar' => $itemData['description_ar'] ?? null,
                        'description_en' => $itemData['description_en'] ?? null,
                        'button_text_ar' => $itemData['button_text_ar'] ?? null,
                        'button_text_en' => $itemData['button_text_en'] ?? null,
                        'image' => $imagePath,
                    ]);
                }
            }
        });

        return redirect()->route('admin.contents.index')->with('success', __('Content created successfully.'));
    }

    public function show($id)
    {
        $item = Content::with('items')->findOrFail($id);
        return view('admin.contents.show', compact('item'));
    }

    public function edit($id)
    {
        $item = Content::with('items')->findOrFail($id);
        return view('admin.contents.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $content = Content::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'title_ar' => 'sometimes|required|string',
            'title_en' => 'sometimes|required|string',
            'is_visible' => 'boolean',
            'items' => 'nullable|array',
            'items.*.id' => 'nullable|exists:content_items,id',
            'items.*.title_ar' => 'required_with:items|string',
            'items.*.title_en' => 'required_with:items|string',
            'items.*.image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        DB::transaction(function () use ($request, $content) {
            $content->update($request->only(['title_ar', 'title_en', 'is_visible']));

            if ($request->has('items')) {
                $requestedIds = collect($request->items)->pluck('id')->filter()->toArray();
                $content->items()->whereNotIn('id', $requestedIds)->delete();

                foreach ($request->items as $index => $itemData) {
                    $imagePath = null;
                    if ($request->hasFile("items.{$index}.image")) {
                        $imagePath = $request->file("items.{$index}.image")->store('content_items', 'public');
                    }

                    if (isset($itemData['id'])) {
                        $item = $content->items()->find($itemData['id']);
                        if ($item) {
                            $updateData = [
                                'title_ar' => $itemData['title_ar'] ?? $item->title_ar,
                                'title_en' => $itemData['title_en'] ?? $item->title_en,
                                'description_ar' => $itemData['description_ar'] ?? $item->description_ar,
                                'description_en' => $itemData['description_en'] ?? $item->description_en,
                                'button_text_ar' => $itemData['button_text_ar'] ?? $item->button_text_ar,
                                'button_text_en' => $itemData['button_text_en'] ?? $item->button_text_en,
                            ];
                            if ($imagePath) {
                                $updateData['image'] = $imagePath;
                            }
                            $item->update($updateData);
                        }
                    } else {
                        $content->items()->create([
                            'title_ar' => $itemData['title_ar'] ?? null,
                            'title_en' => $itemData['title_en'] ?? null,
                            'description_ar' => $itemData['description_ar'] ?? null,
                            'description_en' => $itemData['description_en'] ?? null,
                            'button_text_ar' => $itemData['button_text_ar'] ?? null,
                            'button_text_en' => $itemData['button_text_en'] ?? null,
                            'image' => $imagePath,
                        ]);
                    }
                }
            }
        });

        return redirect()->route('admin.contents.index')->with('success', __('Content updated successfully.'));
    }

    public function destroy($id)
    {
        $content = Content::findOrFail($id);
        $content->delete();
        return redirect()->route('admin.contents.index')->with('success', __('Content deleted successfully.'));
    }
}
