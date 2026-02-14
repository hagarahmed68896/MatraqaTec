<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class SupervisorController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('type', 'supervisor')->with('roles');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status') && $request->status != 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('role_id') && $request->role_id != 'all') {
            $query->whereHas('roles', function ($q) use ($request) {
                $q->where('roles.id', $request->role_id);
            });
        }

        $sort = $request->input('sort', 'newest');
        switch ($sort) {
            case 'name':
                $query->orderBy('name', 'asc');
                break;
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        $items = $query->paginate(10);
        $roles = Role::all();
        return view('admin.supervisors.index', compact('items', 'roles'));
    }

    public function blockedIndex()
    {
        $items = User::where('type', 'supervisor')
            ->where('status', 'blocked')
            ->with('roles')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        return view('admin.supervisors.blocked', compact('items'));
    }

    public function create()
    {
        $roles = Role::all();
        return view('admin.supervisors.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'nullable|string|min:8',
            'phone' => 'required|string|unique:users',
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,id',
            'status' => 'required|in:active,blocked',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user = User::create([
            'name' => $request->first_name . ' ' . $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password ?? \Illuminate\Support\Str::random(10)),
            'type' => 'supervisor',
            'phone' => $request->phone,
            'status' => $request->status,
        ]);

        if ($request->has('roles')) {
            $roles = collect($request->roles)->mapWithKeys(fn($id) => [$id => ['model_type' => User::class]])->toArray();
            $user->roles()->sync($roles);
        }

        return redirect()->route('admin.supervisors.index')->with('success', __('Supervisor created successfully.'));
    }

    public function show($id)
    {
        $item = User::where('type', 'supervisor')->with('roles')->findOrFail($id);
        return view('admin.supervisors.show', compact('item'));
    }

    public function edit($id)
    {
        $item = User::where('type', 'supervisor')->with('roles')->findOrFail($id);
        $roles = Role::all();
        return view('admin.supervisors.edit', compact('item', 'roles'));
    }

    public function update(Request $request, $id)
    {
        $supervisor = User::where('type', 'supervisor')->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'phone' => 'required|string|unique:users,phone,' . $id,
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,id',
            'status' => 'required|in:active,blocked',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $supervisor->name = $request->first_name . ' ' . $request->last_name;
        $supervisor->email = $request->email;
        if ($request->has('password') && $request->password) {
            $supervisor->password = Hash::make($request->password);
        }
        $supervisor->phone = $request->phone;
        if ($request->has('status')) {
            $supervisor->status = $request->status;
        }
        $supervisor->save();

        if ($request->has('roles')) {
            $roles = collect($request->roles)->mapWithKeys(fn($id) => [$id => ['model_type' => User::class]])->toArray();
            $supervisor->roles()->sync($roles);
        }

        return redirect()->route('admin.supervisors.index')->with('success', __('Supervisor updated successfully.'));
    }

    public function destroy($id)
    {
        $supervisor = User::where('type', 'supervisor')->findOrFail($id);
        $supervisor->delete();
        return back()->with('success', __('Supervisor deleted successfully.'));
    }

    public function bulkBlock(Request $request)
    {
        $ids = $request->input('ids');
        if (empty($ids)) {
            return back()->with('error', __('No supervisors selected.'));
        }

        User::where('type', 'supervisor')->whereIn('id', $ids)->update(['status' => 'blocked']);

        return back()->with('success', __('Selected supervisors have been blocked.'));
    }

    public function bulkUnblock(Request $request)
    {
        $ids = $request->input('ids');
        if (empty($ids)) {
            return back()->with('error', __('No supervisors selected.'));
        }

        User::where('type', 'supervisor')->whereIn('id', $ids)->update(['status' => 'active']);

        return back()->with('success', __('Selected supervisors have been unblocked.'));
    }

    public function toggleBlock($id)
    {
        $user = User::where('type', 'supervisor')->findOrFail($id);
        $user->status = $user->status == 'active' ? 'blocked' : 'active';
        $user->save();

        return back()->with('success', __('Supervisor status updated.'));
    }

    public function download()
    {
        $supervisors = User::where('type', 'supervisor')->get();
        return $this->generateCsv($supervisors, "supervisors.csv");
    }

    public function downloadBlocked()
    {
        $supervisors = User::where('type', 'supervisor')->where('status', 'blocked')->get();
        return $this->generateCsv($supervisors, "blocked_supervisors.csv");
    }

    private function generateCsv($users, $filename)
    {
        $handle = fopen('php://memory', 'w');
        fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM
        fputcsv($handle, ['ID', 'Name', 'Email', 'Phone', 'Status', 'Created At']); 

        foreach ($users as $user) {
            fputcsv($handle, [
                $user->id,
                $user->name,
                $user->email,
                $user->phone,
                $user->status == 'active' ? __('Active') : __('Blocked'),
                $user->created_at->format('Y-m-d'),
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
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]
        );
    }
}
