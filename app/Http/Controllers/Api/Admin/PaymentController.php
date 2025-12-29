<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index()
    {
        $payments = Payment::with(['user', 'order'])->get();
        return response()->json(['status' => true, 'message' => 'Payments retrieved', 'data' => $payments]);
    }

    public function store(Request $request)
    {
        $payment = Payment::create($request->all());
        return response()->json(['status' => true, 'message' => 'Payment recorded', 'data' => $payment]);
    }

    public function show($id)
    {
        $payment = Payment::find($id);
        if (!$payment) return response()->json(['status' => false, 'message' => 'Not found'], 404);
        return response()->json(['status' => true, 'message' => 'Payment retrieved', 'data' => $payment]);
    }

    public function update(Request $request, $id)
    {
        $payment = Payment::find($id);
        if (!$payment) return response()->json(['status' => false, 'message' => 'Not found'], 404);
        $payment->update($request->all());
        return response()->json(['status' => true, 'message' => 'Payment updated', 'data' => $payment]);
    }

    public function destroy($id)
    {
        $payment = Payment::find($id);
        if (!$payment) return response()->json(['status' => false, 'message' => 'Not found'], 404);
        $payment->delete();
        return response()->json(['status' => true, 'message' => 'Payment deleted']);
    }
}
