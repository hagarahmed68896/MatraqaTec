<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ComplaintController extends Controller
{
    public function index()
    {
        $complaints = Complaint::with(['order', 'actions'])
            ->where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($complaint) {
                // Map types to localized titles
                $titles = [
                    'general_inquiry' => ['ar' => 'استفسار عام', 'en' => 'General Inquiry'],
                    'complaint_technician' => ['ar' => 'شكوى على فني', 'en' => 'Complaint against Technician'],
                    'payment_issue' => ['ar' => 'مشكلة في الدفع', 'en' => 'Payment Issue'],
                    'suggestion_note' => ['ar' => 'اقتراح / ملاحظة', 'en' => 'Suggestion / Note'],
                ];

                $complaint->title = $titles[$complaint->type]['ar'] ?? $complaint->type; // Default to Arabic or Type
                $complaint->title_en = $titles[$complaint->type]['en'] ?? $complaint->type;
                $complaint->created_ago = $complaint->created_at->diffForHumans(); // e.g. "1 hour ago"
                
                return $complaint;
            });
            
        return response()->json(['status' => true, 'message' => 'My Inquiries retrieved', 'data' => $complaints]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:general_inquiry,complaint_technician,payment_issue,suggestion_note',
            'description' => 'required|string',
            'phone' => 'required|string', // Added phone number
            'order_id' => [
                'nullable',
                'exists:orders,id',
                // Conditional validation: required if type is complaint_technician
                Rule::requiredIf(fn() => $request->type === 'complaint_technician'),
            ],
            'attachment' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first(), 'errors' => $validator->errors()], 422);
        }

        // Handle File Upload
        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('complaints', 'public');
        }

        // Determine Account Type
        $user = auth()->user();
        $accountType = 'client'; // Default
        if ($user->user_type === 'technician') {
            $accountType = 'technician';
        } elseif ($user->user_type === 'company') {
            $accountType = 'company';
        }

        $complaint = Complaint::create([
            'ticket_number' => 'TKT-' . strtoupper(uniqid()),
            'user_id' => auth()->id(),
            'order_id' => $request->order_id,
            'account_type' => $accountType,
            'phone' => $request->phone, // Use provided phone
            'type' => $request->type,
            'description' => $request->description,
            'attachment' => $attachmentPath,
            'status' => 'pending',
        ]);

        return response()->json(['status' => true, 'message' => 'Support requested received successfully', 'data' => $complaint]);
    }

    public function show($id)
    {
        $complaint = Complaint::where('user_id', auth()->id())->find($id);

        if (!$complaint) {
            return response()->json(['status' => false, 'message' => 'Ticket not found'], 404);
        }

        return response()->json(['status' => true, 'message' => 'Ticket details retrieved', 'data' => $complaint]);
    }
}
