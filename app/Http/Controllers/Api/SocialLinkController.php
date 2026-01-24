<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SocialLink;
use Illuminate\Http\Request;

class SocialLinkController extends Controller
{
    public function index()
    {
        $links = SocialLink::all();
        return response()->json(['status' => true, 'message' => 'Social Links retrieved successfully', 'data' => $links]);
    }

}
