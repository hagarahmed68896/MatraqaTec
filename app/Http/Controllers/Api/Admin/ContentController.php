<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Content; // Assuming a general Content model or similar
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ContentController extends Controller
{
    public function index()
    {
        $contents = Content::all();
        return response()->json(['status' => true, 'message' => 'Contents retrieved', 'data' => $contents]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'key' => 'required|string|unique:contents',
            'value_ar' => 'required|string',
            'value_en' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first(), 'errors' => $validator->errors()], 422);
        }

        $content = Content::create($request->all());

        return response()->json(['status' => true, 'message' => 'Content created successfully', 'data' => $content]);
    }

    public function show($id)
    {
        $content = Content::find($id);
        if (!$content) return response()->json(['status' => false, 'message' => 'Content not found'], 404);
        return response()->json(['status' => true, 'message' => 'Content retrieved', 'data' => $content]);
    }

    public function update(Request $request, $id)
    {
        $content = Content::find($id);
        if (!$content) return response()->json(['status' => false, 'message' => 'Content not found'], 404);

        $content->update($request->all());

        return response()->json(['status' => true, 'message' => 'Content updated', 'data' => $content]);
    }

    public function destroy($id)
    {
        $content = Content::find($id);
        if (!$content) return response()->json(['status' => false, 'message' => 'Content not found'], 404);
        $content->delete();
        return response()->json(['status' => true, 'message' => 'Content deleted']);
    }
}
