<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Term;
use Illuminate\Http\Request;

class TermController extends Controller
{
    public function index()
    {
        $terms = Term::all();
        return response()->json(['status' => true, 'message' => 'Terms retrieved successfully', 'data' => $terms]);
    }

// Methods removed. Read-only controller.
}
