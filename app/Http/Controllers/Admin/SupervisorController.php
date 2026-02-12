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

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->has('status') && $request->status != 'all') {
            $query->where('status', $request->status);
        }

        $items = $query->orderBy('created_at', 'desc')->paginate(10);
        return view('admin.supervisors.index', compact('items'));
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
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'phone' => 'nullable|string|unique:users',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'type' => 'supervisor',
            'phone' => $request->phone,
            'status' => 'active',
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
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'phone' => 'nullable|string|unique:users,phone,' . $id,
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $supervisor->name = $request->name;
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
        return redirect()->route('admin.supervisors.index')->with('success', __('Supervisor deleted successfully.'));
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
                $user->status,
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
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]
        );
    }
}
