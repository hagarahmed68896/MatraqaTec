<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Term;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TermController extends Controller
{
    public function index()
    {
        $terms = Term::all();
        return response()->json(['status' => true, 'message' => 'Terms retrieved', 'data' => $terms]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'content_ar' => 'required|string',
            'content_en' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first(), 'errors' => $validator->errors()], 422);
        }

        $term = Term::create($request->all());

        return response()->json(['status' => true, 'message' => 'Term created successfully', 'data' => $term]);
    }

    public function show($id)
    {
        $term = Term::find($id);
        if (!$term) return response()->json(['status' => false, 'message' => 'Term not found'], 404);
        return response()->json(['status' => true, 'message' => 'Term retrieved', 'data' => $term]);
    }

    public function update(Request $request, $id)
    {
        $term = Term::find($id);
        if (!$term) return response()->json(['status' => false, 'message' => 'Term not found'], 404);

        $term->update($request->all());

        return response()->json(['status' => true, 'message' => 'Term updated', 'data' => $term]);
    }

    public function destroy($id)
    {
        $term = Term::find($id);
        if (!$term) return response()->json(['status' => false, 'message' => 'Term not found'], 404);
        $term->delete();
        return response()->json(['status' => true, 'message' => 'Term deleted']);
    }
}
