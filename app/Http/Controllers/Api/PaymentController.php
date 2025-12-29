<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index()
    {
        $payments = Payment::with(['user', 'order'])
            ->where('user_id', auth()->id())
            ->get();
        return response()->json(['status' => true, 'message' => 'Payments retrieved', 'data' => $payments]);
    }

    public function store(Request $request)
    {
        $request->merge(['user_id' => auth()->id()]);
        $payment = Payment::create($request->all());
        return response()->json(['status' => true, 'message' => 'Payment recorded', 'data' => $payment]);
    }

    public function show($id)
    {
        $payment = Payment::where('user_id', auth()->id())->where('id', $id)->first();
        if (!$payment) return response()->json(['status' => false, 'message' => 'Not found'], 404);
        return response()->json(['status' => true, 'message' => 'Payment retrieved', 'data' => $payment]);
    }
    
    // Update/Destroy removed (Admin/System managed)
}
