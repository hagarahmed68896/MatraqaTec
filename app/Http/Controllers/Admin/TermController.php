<?php

namespace App\Http\Controllers\Admin;

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
        $items = $query->paginate($perPage);

        return view('admin.terms.index', compact('items'));
    }

    public function create()
    {
        return view('admin.terms.create');
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
            return back()->withErrors($validator)->withInput();
        }

        Term::create($request->all());

        return redirect()->route('admin.terms.index')->with('success', __('Term created successfully.'));
    }

    public function show($id)
    {
        $item = Term::findOrFail($id);
        return view('admin.terms.show', compact('item'));
    }

    public function edit($id)
    {
        $item = Term::findOrFail($id);
        return view('admin.terms.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $term = Term::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'title_ar' => 'required|string',
            'title_en' => 'required|string',
            'content_ar' => 'required|string',
            'content_en' => 'required|string',
            'target_group' => 'nullable|in:clients,companies,technicians,all',
            'status' => 'nullable|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $term->update($request->all());

        return redirect()->route('admin.terms.index')->with('success', __('Term updated successfully.'));
    }

    public function destroy($id)
    {
        $term = Term::findOrFail($id);
        $term->delete();
        return redirect()->route('admin.terms.index')->with('success', __('Term deleted successfully.'));
    }

    public function bulkDestroy(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array',
            'ids.*' => 'exists:terms,id',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        Term::whereIn('id', $request->ids)->delete();

        return redirect()->route('admin.terms.index')->with('success', __('Selected terms deleted successfully.'));
    }
}
