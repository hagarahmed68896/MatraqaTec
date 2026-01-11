<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FaqController extends Controller
{
    public function index(Request $request)
    {
        $query = Faq::query();

        // 1. Search Logic (Search in both AR and EN questions/answers)
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('question_ar', 'like', "%{$search}%")
                  ->orWhere('question_en', 'like', "%{$search}%")
                  ->orWhere('answer_ar', 'like', "%{$search}%")
                  ->orWhere('answer_en', 'like', "%{$search}%");
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

        // 4. Sorting Logic
        if ($request->has('sort_by')) {
            switch ($request->sort_by) {
                case 'name':
                    $query->orderBy('question_ar', 'asc');
                    break;
                case 'oldest':
                    $query->orderBy('created_at', 'asc');
                    break;
                case 'newest':
                default:
                    $query->orderBy('created_at', 'desc');
                    break;
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        // 5. Pagination
        $perPage = $request->get('per_page', 10);
        $faqs = $query->paginate($perPage);

        return response()->json(['status' => true, 'message' => 'Faqs retrieved', 'data' => $faqs]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'question_ar' => 'required|string',
            'question_en' => 'required|string',
            'answer_ar' => 'required|string',
            'answer_en' => 'required|string',
            'target_group' => 'required|in:clients,companies,technicians,all',
            'status' => 'required|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first(), 'errors' => $validator->errors()], 422);
        }

        $faq = Faq::create($request->all());

        return response()->json(['status' => true, 'message' => 'Faq created successfully', 'data' => $faq]);
    }

    public function show($id)
    {
        $faq = Faq::find($id);
        if (!$faq) return response()->json(['status' => false, 'message' => 'Faq not found'], 404);
        return response()->json(['status' => true, 'message' => 'Faq retrieved', 'data' => $faq]);
    }

    public function update(Request $request, $id)
    {
        $faq = Faq::find($id);
        if (!$faq) return response()->json(['status' => false, 'message' => 'Faq not found'], 404);

        $faq->update($request->all());

        return response()->json(['status' => true, 'message' => 'Faq updated', 'data' => $faq]);
    }

    public function destroy($id)
    {
        $faq = Faq::find($id);
        if (!$faq) return response()->json(['status' => false, 'message' => 'Faq not found'], 404);
        $faq->delete();
        return response()->json(['status' => true, 'message' => 'Faq deleted']);
    }

    public function bulkDestroy(Request $request)
    {
        $ids = $request->ids;
        if (!is_array($ids)) {
            return response()->json(['status' => false, 'message' => 'IDs must be an array'], 422);
        }

        $count = Faq::whereIn('id', $ids)->delete();

        return response()->json(['status' => true, 'message' => "$count Faqs deleted successfully"]);
    }

    public function download()
    {
        $faqs = Faq::all();
        
        $handle = fopen('php://memory', 'w');
        // Add UTF-8 BOM for Arabic support in Excel
        fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
        
        fputcsv($handle, ['ID', 'Question (AR)', 'Question (EN)', 'Target Group', 'Status', 'Date']);

        foreach ($faqs as $faq) {
            fputcsv($handle, [
                $faq->id,
                $faq->question_ar,
                $faq->question_en,
                $faq->target_group,
                $faq->status,
                $faq->created_at->format('Y-m-d'),
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
                'Content-Disposition' => 'attachment; filename="faqs.csv"',
            ]
        );
    }
}
