<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class InventoryController extends Controller
{
    public function index()
    {
        $inventory = Inventory::all();
        return response()->json(['status' => true, 'message' => 'Inventory retrieved successfully', 'data' => $inventory]);
    }

// Methods removed. Read-only controller.
}
