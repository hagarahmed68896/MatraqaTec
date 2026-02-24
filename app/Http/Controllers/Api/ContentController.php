<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Content;
use Illuminate\Http\Request;

class ContentController extends Controller
{
    public function index()
    {
        $content = Content::with('items')->where('is_visible', true)->get();
        return response()->json(['status' => true, 'message' => 'Content retrieved successfully', 'data' => $content]);
    }

}
