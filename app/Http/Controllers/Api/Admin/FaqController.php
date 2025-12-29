<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FaqController extends Controller
{
    public function index()
    {
        $faqs = Faq::all();
        return response()->json(['status' => true, 'message' => 'Faqs retrieved', 'data' => $faqs]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'question_ar' => 'required|string',
            'question_en' => 'required|string',
            'answer_ar' => 'required|string',
            'answer_en' => 'required|string',
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
}
