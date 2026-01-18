<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ComplaintController extends Controller
{
    public function index(Request $request)
    {
        $query = Complaint::with(['order', 'actions'])
            ->where('user_id', auth()->id());

        // Search by ticket number or order ID
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                  ->orWhere('order_id', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $complaints = $query->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($complaint) {
                // Map types to localized titles
                $titles = [
                    'general_inquiry' => ['ar' => 'استفسار عام', 'en' => 'General Inquiry'],
                    'complaint_technician' => ['ar' => 'شكوى على فني', 'en' => 'Complaint against Technician'],
                    'complaint_customer' => ['ar' => 'شكوى على عميل', 'en' => 'Complaint against Customer'],
                    'payment_issue' => ['ar' => 'مشكلة في الدفع', 'en' => 'Payment Issue'],
                    'suggestion_note' => ['ar' => 'اقتراح / ملاحظة', 'en' => 'Suggestion / Note'],
                ];

                $complaint->title = $titles[$complaint->type]['ar'] ?? $complaint->type;
                $complaint->title_en = $titles[$complaint->type]['en'] ?? $complaint->type;
                $complaint->created_ago = $complaint->created_at->diffForHumans();
                
                return $complaint;
            });
            
        return response()->json(['status' => true, 'message' => 'Inquiries retrieved', 'data' => $complaints]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:general_inquiry,complaint_technician,complaint_customer,payment_issue,suggestion_note',
            'description' => 'required|string',
            'phone' => 'required|string',
            'order_id' => [
                'nullable',
                'exists:orders,id',
                Rule::requiredIf(fn() => in_array($request->type, ['complaint_technician', 'complaint_customer'])),
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
        $accountType = $user->type ?: 'client'; // Use 'type' column from users table

        $complaint = Complaint::create([
            'ticket_number' => 'TKT-' . strtoupper(uniqid()),
            'user_id' => auth()->id(),
            'order_id' => $request->order_id,
            'account_type' => $accountType,
            'phone' => $request->phone,
            'type' => $request->type,
            'description' => $request->description,
            'attachment' => $attachmentPath,
            'status' => 'pending',
        ]);

        return response()->json(['status' => true, 'message' => 'Ticket created successfully', 'data' => $complaint]);
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
