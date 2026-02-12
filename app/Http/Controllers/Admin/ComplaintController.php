<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\ComplaintAction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ComplaintController extends Controller
{
    /**
     * List all complaints with filtering, searching, and pagination.
     */
    public function index(Request $request)
    {
        $query = Complaint::with(['user', 'order']);

        // 1. Search Logic (Ticket Number, User Name, Phone)
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // 2. Filters (Using English internal values)
        if ($request->has('account_type') && $request->account_type != 'all') {
            $query->where('account_type', $request->account_type);
        }

        if ($request->has('type') && $request->type != 'all') {
            $query->where('type', $request->type);
        }

        if ($request->has('status') && $request->status != 'all') {
            $query->where('status', $request->status);
        }

        // 3. Sorting
        if ($request->has('sort_by')) {
            switch ($request->sort_by) {
                case 'name':
                    $query->join('users', 'complaints.user_id', '=', 'users.id')
                          ->orderBy('users.name', 'asc')
                          ->select('complaints.*');
                    break;
                case 'oldest':
                    $query->orderBy('created_at', 'asc');
                    break;
                case 'newest':
                default:
                    $query->orderBy('created_at', 'desc');
                    break;
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $perPage = $request->get('per_page', 10);
        $items = $query->paginate($perPage);

        return view('admin.complaints.index', compact('items'));
    }

    /**
     * Show details of a single complaint including actions.
     */
    public function show($id)
    {
        $item = Complaint::with(['user', 'order', 'actions.admin'])->findOrFail($id);
        return view('admin.complaints.show', compact('item'));
    }

    /**
     * Perform an action on a complaint.
     */
    public function takeAction(Request $request, $id)
    {
        $complaint = Complaint::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'action_type' => 'required|in:warning,suspension,rejection,clarification,refund',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        DB::transaction(function () use ($request, $complaint) {
            ComplaintAction::create([
                'complaint_id' => $complaint->id,
                'action_type' => $request->action_type,
                'notes' => $request->notes,
                'admin_id' => auth()->id() ?? 1,
            ]);

            // Side effects based on action
            switch ($request->action_type) {
                case 'rejection':
                    $complaint->status = 'rejected';
                    break;
                case 'refund':
                    $complaint->status = 'resolved';
                    break;
            }
            
            $complaint->save();

            // If suspension, block user temporary
            if ($request->action_type == 'suspension' && $complaint->user) {
                $complaint->user->update(['status' => 'blocked']);
            }
        });

        return back()->with('success', __('Action performed successfully.'));
    }

    /**
     * Download complaints as CSV.
     */
    public function download()
    {
        $complaints = Complaint::with('user')->get();
        
        $handle = fopen('php://memory', 'w');
        fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM
        
        fputcsv($handle, ['Ticket #', 'User', 'Account Type', 'Phone', 'Type', 'Status', 'Date']);

        foreach ($complaints as $complaint) {
            fputcsv($handle, [
                $complaint->ticket_number,
                $complaint->user ? $complaint->user->name : '',
                $complaint->account_type,
                $complaint->phone,
                $complaint->type,
                $complaint->status,
                $complaint->created_at->format('Y-m-d'),
            ]);
        }

        fseek($handle, 0);

        return response()->stream(
            function () use ($handle) {
                fpassthru($handle);
                fclose($handle);
            },
            200,
            [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="complaints.csv"',
            ]
        );
    }
}
