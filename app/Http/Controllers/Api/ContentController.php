<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Content;
use Illuminate\Http\Request;

class ContentController extends Controller
{
    public function index()
    {
        $content = Content::all();
        return response()->json(['status' => true, 'message' => 'Content retrieved successfully', 'data' => $content]);
    }

// Methods removed. Read-only controller.
}
