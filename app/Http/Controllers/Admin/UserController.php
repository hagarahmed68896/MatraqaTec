<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        // Filter by Type
        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }

        // Filter by Status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Search Logic
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $items = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.users.index', compact('items'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|unique:users,phone',
            'password' => 'required|string|min:8',
            'type' => 'required|in:customer,technician,maintenance_company',
            'status' => 'required|in:active,blocked,pending',
        ]);

        $validated['password'] = Hash::make($validated['password']);

        User::create($validated);

        return redirect()->route('admin.users.index')->with('success', __('User created successfully.'));
    }

    public function show($id)
    {
        $item = User::findOrFail($id);
        return view('admin.users.show', compact('item'));
    }

    public function edit($id)
    {
        $item = User::findOrFail($id);
        return view('admin.users.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'required|string|unique:users,phone,' . $user->id,
            'type' => 'required|in:customer,technician,maintenance_company',
            'status' => 'required|in:active,blocked,pending',
        ]);

        if ($request->filled('password')) {
            $request->validate([
                'password' => 'string|min:8',
            ]);
            $validated['password'] = Hash::make($request->password);
        }

        $user->update($validated);

        return redirect()->route('admin.users.index')->with('success', __('User updated successfully.'));
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        // Prevent deleting self if needed or check logic
        $user->delete();
        return redirect()->route('admin.users.index')->with('success', __('User deleted successfully.'));
    }

    // Extra Actions

    public function toggleBlock($id)
    {
        $user = User::findOrFail($id);

        if ($user->status === 'blocked') {
            $user->status = 'active';
            $user->blocked_at = null;
            $message = __('User unblocked successfully');
        } else {
            $user->status = 'blocked';
            $user->blocked_at = now();
            $message = __('User blocked successfully');
        }
        $user->save();

        return back()->with('success', $message);
    }

    public function downloadAllBlocked()
    {
        $blockedUsers = User::where('status', 'blocked')->orderBy('type')->orderBy('created_at', 'desc')->get();
        return $this->generateCsv($blockedUsers, "all_blocked_users.csv");
    }

    private function generateCsv($users, $filename)
    {
        $handle = fopen('php://memory', 'w');
        fputcsv($handle, ['ID', 'Name', 'Email', 'Phone', 'Type', 'Blocked At', 'Created At']); 
        
        // Add BOM for Excel UTF-8 compatibility
        fputs($handle, "\xEF\xBB\xBF");

        foreach ($users as $user) {
            fputcsv($handle, [
                $user->id,
                $user->name,
                $user->email,
                $user->phone,
                $user->type,
                $user->blocked_at,
                $user->created_at,
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
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]
        );
    }
}
