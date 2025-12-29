<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\SocialLink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SocialLinkController extends Controller
{
    public function index()
    {
        $links = SocialLink::all();
        return response()->json(['status' => true, 'message' => 'Links retrieved', 'data' => $links]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'url' => 'required|url',
            'icon' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first(), 'errors' => $validator->errors()], 422);
        }

        $link = SocialLink::create($request->all());

        return response()->json(['status' => true, 'message' => 'Link created successfully', 'data' => $link]);
    }

    public function show($id)
    {
        $link = SocialLink::find($id);
        if (!$link) return response()->json(['status' => false, 'message' => 'Link not found'], 404);
        return response()->json(['status' => true, 'message' => 'Link retrieved', 'data' => $link]);
    }

    public function update(Request $request, $id)
    {
        $link = SocialLink::find($id);
        if (!$link) return response()->json(['status' => false, 'message' => 'Link not found'], 404);

        $link->update($request->all());

        return response()->json(['status' => true, 'message' => 'Link updated', 'data' => $link]);
    }

    public function destroy($id)
    {
        $link = SocialLink::find($id);
        if (!$link) return response()->json(['status' => false, 'message' => 'Link not found'], 404);
        $link->delete();
        return response()->json(['status' => true, 'message' => 'Link deleted']);
    }
}
