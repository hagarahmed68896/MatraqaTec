<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class SupervisorController extends Controller
{
    public function index()
    {
        $supervisors = User::where('type', 'supervisor')->with('roles')->orderBy('created_at', 'desc')->paginate(10);
        return response()->json(['status' => true, 'message' => 'Supervisors retrieved', 'data' => $supervisors]);
    }

    public function blockedIndex()
    {
        $supervisors = User::where('type', 'supervisor')
            ->where('status', 'blocked')
            ->with('roles')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        return response()->json(['status' => true, 'message' => 'Blocked supervisors retrieved', 'data' => $supervisors]);
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
            return response()->json(['status' => false, 'message' => $validator->errors()->first(), 'errors' => $validator->errors()], 422);
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

        return response()->json(['status' => true, 'message' => 'Supervisor created successfully', 'data' => $user->load('roles')]);
    }

    public function show($id)
    {
        $supervisor = User::where('type', 'supervisor')->with('roles')->find($id);
        if (!$supervisor) return response()->json(['status' => false, 'message' => 'Supervisor not found'], 404);
        return response()->json(['status' => true, 'message' => 'Supervisor retrieved', 'data' => $supervisor]);
    }

    public function update(Request $request, $id)
    {
        $supervisor = User::where('type', 'supervisor')->find($id);
        if (!$supervisor) return response()->json(['status' => false, 'message' => 'Supervisor not found'], 404);

        $validator = Validator::make($request->all(), [
            'email' => 'nullable|string|email|max:255|unique:users,email,' . $id,
            'phone' => 'nullable|string|unique:users,phone,' . $id,
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first(), 'errors' => $validator->errors()], 422);
        }

        if ($request->has('name')) $supervisor->name = $request->name;
        if ($request->has('email')) $supervisor->email = $request->email;
        if ($request->has('password') && $request->password) $supervisor->password = Hash::make($request->password);
        if ($request->has('phone')) $supervisor->phone = $request->phone;
        if ($request->has('status')) $supervisor->status = $request->status;
        $supervisor->save();

        if ($request->has('roles')) {
            $roles = collect($request->roles)->mapWithKeys(fn($id) => [$id => ['model_type' => User::class]])->toArray();
            $supervisor->roles()->sync($roles);
        }

        return response()->json(['status' => true, 'message' => 'Supervisor updated', 'data' => $supervisor->load('roles')]);
    }

    public function destroy($id)
    {
        $supervisor = User::where('type', 'supervisor')->find($id);
        if (!$supervisor) return response()->json(['status' => false, 'message' => 'Supervisor not found'], 404);

        $supervisor->delete();

        return response()->json(['status' => true, 'message' => 'Supervisor deleted successfully']);
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
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]
        );
    }
}
