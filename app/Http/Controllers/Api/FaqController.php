<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    public function index()
    {
        $faqs = Faq::all();
        return response()->json(['status' => true, 'message' => 'FAQs retrieved successfully', 'data' => $faqs]);
    }

}
