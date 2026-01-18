<?php

namespace App\Http\Controllers\Api\Admin;

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
        $policies = $query->paginate($perPage);

        return response()->json(['status' => true, 'message' => 'Privacy Policies retrieved', 'data' => $policies]);
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
            return response()->json(['status' => false, 'message' => $validator->errors()->first(), 'errors' => $validator->errors()], 422);
        }

        $policy = PrivacyPolicy::create($request->all());

        return response()->json(['status' => true, 'message' => 'Privacy Policy created successfully', 'data' => $policy]);
    }

    public function show($id)
    {
        $policy = PrivacyPolicy::find($id);
        if (!$policy) return response()->json(['status' => false, 'message' => 'Privacy Policy not found'], 404);
        return response()->json(['status' => true, 'message' => 'Privacy Policy retrieved', 'data' => $policy]);
    }

    public function update(Request $request, $id)
    {
        $policy = PrivacyPolicy::find($id);
        if (!$policy) return response()->json(['status' => false, 'message' => 'Privacy Policy not found'], 404);

        $policy->update($request->all());

        return response()->json(['status' => true, 'message' => 'Privacy Policy updated', 'data' => $policy]);
    }

    public function destroy($id)
    {
        $policy = PrivacyPolicy::find($id);
        if (!$policy) return response()->json(['status' => false, 'message' => 'Privacy Policy not found'], 404);
        $policy->delete();
        return response()->json(['status' => true, 'message' => 'Privacy Policy deleted']);
    }

    public function bulkDestroy(Request $request)
    {
        $ids = $request->ids;
        if (!is_array($ids)) {
            return response()->json(['status' => false, 'message' => 'IDs must be an array'], 422);
        }

        $count = PrivacyPolicy::whereIn('id', $ids)->delete();

        return response()->json(['status' => true, 'message' => "$count Privacy Policies deleted successfully"]);
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
