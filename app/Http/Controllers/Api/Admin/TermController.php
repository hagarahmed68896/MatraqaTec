<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Term;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TermController extends Controller
{
    public function index(Request $request)
    {
        $query = Term::query();

        // 1. Search
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title_ar', 'like', "%{$search}%")
                  ->orWhere('title_en', 'like', "%{$search}%")
                  ->orWhere('content_ar', 'like', "%{$search}%")
                  ->orWhere('content_en', 'like', "%{$search}%");
            });
        }

        // 2. Filters
        if ($request->filled('target_group')) {
            $query->where('target_group', $request->input('target_group'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // 3. Sorting
        $sortBy = $request->input('sort_by', 'latest');
        switch ($sortBy) {
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            case 'name_ar':
                $query->orderBy('title_ar', 'asc');
                break;
            case 'name_en':
                $query->orderBy('title_en', 'asc');
                break;
            case 'latest':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        // 4. Pagination
        $perPage = $request->input('per_page', 10);
        $terms = $query->paginate($perPage);

        return response()->json([
            'status' => true,
            'message' => 'Terms retrieved',
            'data' => $terms
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title_ar' => 'required|string',
            'title_en' => 'required|string',
            'content_ar' => 'required|string',
            'content_en' => 'required|string',
            'target_group' => 'nullable|in:clients,companies,technicians,all',
            'status' => 'nullable|in:active,inactive',
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

        $validator = Validator::make($request->all(), [
            'title_ar' => 'sometimes|string',
            'title_en' => 'sometimes|string',
            'content_ar' => 'sometimes|string',
            'content_en' => 'sometimes|string',
            'target_group' => 'sometimes|in:clients,companies,technicians,all',
            'status' => 'sometimes|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first(), 'errors' => $validator->errors()], 422);
        }

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

    public function bulkDestroy(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array',
            'ids.*' => 'exists:terms,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first(), 'errors' => $validator->errors()], 422);
        }

        Term::whereIn('id', $request->ids)->delete();

        return response()->json(['status' => true, 'message' => 'Selected terms deleted successfully']);
    }
}
