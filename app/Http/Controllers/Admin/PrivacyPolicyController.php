<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PrivacyPolicy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PrivacyPolicyController extends Controller
{
    public function index(Request $request)
    {
        $query = PrivacyPolicy::query();

        // 1. Search Logic
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title_ar', 'like', "%{$search}%")
                  ->orWhere('title_en', 'like', "%{$search}%");
            });
        }

        // 2. Filter by Target Group
        if ($request->has('target_group') && $request->target_group != 'all') {
            $query->where('target_group', $request->target_group);
        }

        // 3. Filter by Status
        if ($request->has('status') && $request->status != 'all') {
            $status = $request->status == 'active' ? 'active' : 'inactive';
            $query->where('status', $status);
        }

        // 4. Sorting logic
        $query->orderBy('created_at', 'desc');

        // 5. Pagination
        $perPage = $request->get('per_page', 10);
        $items = $query->paginate($perPage);

        return view('admin.privacy_policies.index', compact('items'));
    }

    public function create()
    {
        return view('admin.privacy_policies.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title_ar' => 'required|string',
            'title_en' => 'required|string',
            'content_ar' => 'required|string',
            'content_en' => 'required|string',
            'target_group' => 'required|in:clients,companies,technicians,all',
            'status' => 'required|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        PrivacyPolicy::create($request->all());

        return redirect()->route('admin.privacy-policies.index')->with('success', __('Privacy Policy created successfully.'));
    }

    public function show($id)
    {
        $item = PrivacyPolicy::findOrFail($id);
        return view('admin.privacy_policies.show', compact('item'));
    }

    public function edit($id)
    {
        $item = PrivacyPolicy::findOrFail($id);
        return view('admin.privacy_policies.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $policy = PrivacyPolicy::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'title_ar' => 'required|string',
            'title_en' => 'required|string',
            'content_ar' => 'required|string',
            'content_en' => 'required|string',
            'target_group' => 'required|in:clients,companies,technicians,all',
            'status' => 'required|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $policy->update($request->all());

        return redirect()->route('admin.privacy-policies.index')->with('success', __('Privacy Policy updated successfully.'));
    }

    public function destroy($id)
    {
        $policy = PrivacyPolicy::findOrFail($id);
        $policy->delete();
        return redirect()->route('admin.privacy-policies.index')->with('success', __('Privacy Policy deleted successfully.'));
    }

    public function bulkDestroy(Request $request)
    {
        $ids = $request->ids;
        if (!is_array($ids)) {
            return back()->with('error', __('IDs must be an array.'));
        }

        PrivacyPolicy::whereIn('id', $ids)->delete();

        return redirect()->route('admin.privacy-policies.index')->with('success', __('Privacy Policies deleted successfully.'));
    }

    public function download()
    {
        $policies = PrivacyPolicy::all();
        
        $handle = fopen('php://memory', 'w');
        fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM
        
        fputcsv($handle, ['ID', 'Title (AR)', 'Title (EN)', 'Target Group', 'Status', 'Date']);

        foreach ($policies as $policy) {
            fputcsv($handle, [
                $policy->id,
                $policy->title_ar,
                $policy->title_en,
                $policy->target_group,
                $policy->status,
                $policy->created_at->format('Y-m-d'),
            ]);
        }

        fseek($handle, 0);

        return response()->stream(
            function () use ($handle) {
                fpassthru($handle);
                fclose($handle);
            },
            200,
            [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="privacy_policies.csv"',
            ]
        );
    }
}
