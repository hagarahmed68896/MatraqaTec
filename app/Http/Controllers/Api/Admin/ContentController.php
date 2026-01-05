<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Content; // Assuming a general Content model or similar
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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

        $contents = $query->orderBy('id', 'desc')->paginate(10);
        return response()->json(['status' => true, 'message' => 'Contents retrieved', 'data' => $contents]);
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
            return response()->json(['status' => false, 'message' => $validator->errors()->first(), 'errors' => $validator->errors()], 422);
        }

        $content = \Illuminate\Support\Facades\DB::transaction(function () use ($request) {
            // Create Parent
            $content = Content::create($request->only(['title_ar', 'title_en', 'is_visible']));

            // Create Children (Items)
            if ($request->has('items')) {
                foreach ($request->items as $index => $itemData) {
                    // Handle Image Upload for this specific item
                    // Note: In FormData, array of objects with files is tricky.
                    // Usually sent as items[0][title_ar], items[0][image] (file)
                    
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
            return $content;
        });

        return response()->json(['status' => true, 'message' => 'Content created successfully', 'data' => $content->load('items')]);
    }

    public function show($id)
    {
        $content = Content::with('items')->find($id);
        if (!$content) return response()->json(['status' => false, 'message' => 'Content not found'], 404);
        return response()->json(['status' => true, 'message' => 'Content retrieved', 'data' => $content]);
    }

    public function update(Request $request, $id)
    {
        $content = Content::find($id);
        if (!$content) return response()->json(['status' => false, 'message' => 'Content not found'], 404);

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
            return response()->json(['status' => false, 'message' => $validator->errors()->first(), 'errors' => $validator->errors()], 422);
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($request, $content) {
            $content->update($request->only(['title_ar', 'title_en', 'is_visible']));

            if ($request->has('items')) {
                // Sync Logic:
                // 1. Get IDs from request
                $requestedIds = collect($request->items)->pluck('id')->filter()->toArray();
                
                // 2. Delete items not in request
                $content->items()->whereNotIn('id', $requestedIds)->delete();

                // 3. Update or Create
                foreach ($request->items as $index => $itemData) {
                    $imagePath = null;
                    if ($request->hasFile("items.{$index}.image")) {
                        $imagePath = $request->file("items.{$index}.image")->store('content_items', 'public');
                    }

                    if (isset($itemData['id'])) {
                        // Update
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
                        // Create
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

        return response()->json(['status' => true, 'message' => 'Content updated', 'data' => $content->load('items')]);
    }

    public function destroy($id)
    {
        $content = Content::find($id);
        if (!$content) return response()->json(['status' => false, 'message' => 'Content not found'], 404);
        
        // delete images from storage if needed
        
        $content->delete();
        return response()->json(['status' => true, 'message' => 'Content deleted']);
    }
}
