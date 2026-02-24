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
            'title_en' => 'nullable|string',
            'is_visible' => 'boolean',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $items = [];
        if ($request->filled('items_json')) {
            $items = json_decode($request->input('items_json'), true) ?? [];
        }

        DB::transaction(function () use ($request, $items) {
            $content = Content::create($request->only(['title_ar', 'title_en', 'is_visible']));

            foreach ($items as $index => $itemData) {
                $imagePath = null;
                $imagePreview = $itemData['imagePreview'] ?? null;
                if ($imagePreview && str_starts_with($imagePreview, 'data:image')) {
                    $imageData = $imagePreview;
                    $extension = explode('/', explode(':', substr($imageData, 0, strpos($imageData, ';')))[1])[1];
                    $replace = substr($imageData, 0, strpos($imageData, ',') + 1);
                    $image = str_replace($replace, '', $imageData);
                    $image = str_replace(' ', '+', $image);
                    $imageName = 'content_' . time() . '_' . $index . '.' . $extension;
                    $dir = public_path('content_images');
                    if (!is_dir($dir)) mkdir($dir, 0755, true);
                    file_put_contents($dir . '/' . $imageName, base64_decode($image));
                    $imagePath = $imageName;
                }

                $content->items()->create([
                    'title_ar'       => $itemData['title_ar'] ?? null,
                    'title_en'       => $itemData['title_en'] ?? null,
                    'description_ar' => $itemData['description_ar'] ?? null,
                    'description_en' => $itemData['description_en'] ?? null,
                    'button_text_ar' => $itemData['button_text_ar'] ?? null,
                    'button_text_en' => $itemData['button_text_en'] ?? null,
                    'image'          => $imagePath,
                ]);
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
            'title_en' => 'sometimes|nullable|string',
            'is_visible' => 'boolean',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $items = [];
        if ($request->filled('items_json')) {
            $items = json_decode($request->input('items_json'), true) ?? [];
        }

        DB::transaction(function () use ($request, $content, $items) {
            $content->update($request->only(['title_ar', 'title_en', 'is_visible']));

            // Delete items that were removed
            $requestedIds = collect($items)->pluck('id')->filter()->toArray();
            $content->items()->whereNotIn('id', $requestedIds)->delete();

            foreach ($items as $index => $itemData) {
                $imagePath = null;
                $imagePreview = $itemData['imagePreview'] ?? null;
                if ($imagePreview && str_starts_with($imagePreview, 'data:image')) {
                    $imageData = $imagePreview;
                    $extension = explode('/', explode(':', substr($imageData, 0, strpos($imageData, ';')))[1])[1];
                    $replace = substr($imageData, 0, strpos($imageData, ',') + 1);
                    $image = str_replace($replace, '', $imageData);
                    $image = str_replace(' ', '+', $image);
                    $imageName = 'content_' . time() . '_' . $index . '.' . $extension;
                    $dir = public_path('content_images');
                    if (!is_dir($dir)) mkdir($dir, 0755, true);
                    file_put_contents($dir . '/' . $imageName, base64_decode($image));
                    $imagePath = $imageName;
                }

                if (!empty($itemData['id'])) {
                    // Update existing item
                    $item = $content->items()->find($itemData['id']);
                    if ($item) {
                        $updateData = [
                            'title_ar'       => $itemData['title_ar'] ?? $item->title_ar,
                            'title_en'       => $itemData['title_en'] ?? $item->title_en,
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
                    // Create new item
                    $content->items()->create([
                        'title_ar'       => $itemData['title_ar'] ?? null,
                        'title_en'       => $itemData['title_en'] ?? null,
                        'description_ar' => $itemData['description_ar'] ?? null,
                        'description_en' => $itemData['description_en'] ?? null,
                        'button_text_ar' => $itemData['button_text_ar'] ?? null,
                        'button_text_en' => $itemData['button_text_en'] ?? null,
                        'image'          => $imagePath,
                    ]);
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
