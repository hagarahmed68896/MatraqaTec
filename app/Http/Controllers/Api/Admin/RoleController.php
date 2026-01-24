<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str; // Added for Str::slug

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::withCount(['users as supervisors_count' => function ($query) {
            $query->where('type', 'supervisor');
        }])->orderBy('created_at', 'desc')->get();
        
        return response()->json(['status' => true, 'message' => 'Roles retrieved', 'data' => $roles]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'description_ar' => 'nullable|string',
            'description_en' => 'nullable|string',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first(), 'errors' => $validator->errors()], 422);
        }

        $name = $request->name ?? Str::slug($request->name_en);

        $role = Role::create([
            'name' => $name,
            'name_ar' => $request->name_ar,
            'name_en' => $request->name_en,
            'description_ar' => $request->description_ar,
            'description_en' => $request->description_en,
            'guard_name' => 'api',
        ]);

        if ($request->has('permissions')) {
            $role->permissions()->sync($request->permissions);
        }

        return response()->json(['status' => true, 'message' => 'Role created successfully', 'data' => $role->load('permissions')]);
    }

    public function show($id)
    {
        $role = Role::with('permissions')->find($id);
        if (!$role) return response()->json(['status' => false, 'message' => 'Role not found'], 404);
        return response()->json(['status' => true, 'message' => 'Role retrieved', 'data' => $role]);
    }

    public function update(Request $request, $id)
    {
        $role = Role::find($id);
        if (!$role) return response()->json(['status' => false, 'message' => 'Role not found'], 404);

        $validator = Validator::make($request->all(), [
            'name_ar' => 'nullable|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'description_ar' => 'nullable|string',
            'description_en' => 'nullable|string',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first(), 'errors' => $validator->errors()], 422);
        }

        if ($request->has('name_ar')) $role->name_ar = $request->name_ar;
        if ($request->has('name_en')) {
            $role->name_en = $request->name_en;
            if (!$request->has('name')) $role->name = Str::slug($request->name_en);
        }
        if ($request->has('name')) $role->name = $request->name;
        if ($request->has('description_ar')) $role->description_ar = $request->description_ar;
        if ($request->has('description_en')) $role->description_en = $request->description_en;
        $role->save();

        if ($request->has('permissions')) {
            $role->permissions()->sync($request->permissions);
        }

        return response()->json(['status' => true, 'message' => 'Role updated successfully', 'data' => $role->load('permissions')]);
    }

    public function destroy($id)
    {
        $role = Role::find($id);
        if (!$role) return response()->json(['status' => false, 'message' => 'Role not found'], 404);
        
        $role->delete();

        return response()->json(['status' => true, 'message' => 'Role deleted successfully']);
    }

    public function download()
    {
        $roles = Role::withCount(['users as supervisors_count' => function ($query) {
            $query->where('type', 'supervisor');
        }])->get();

        $handle = fopen('php://memory', 'w');
        fputcsv($handle, ['ID', 'Name (AR)', 'Name (EN)', 'Description (AR)', 'Description (EN)', 'Supervisors Count', 'Date']); 

        foreach ($roles as $role) {
            fputcsv($handle, [
                $role->id,
                $role->name_ar,
                $role->name_en,
                $role->description_ar,
                $role->description_en,
                $role->supervisors_count,
                $role->created_at->format('Y-m-d'),
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
                'Content-Disposition' => 'attachment; filename="roles.csv"',
            ]
        );
    }
}
