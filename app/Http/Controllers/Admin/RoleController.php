<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class RoleController extends Controller
{
    public function index()
    {
        $items = Role::withCount(['users as supervisors_count' => function ($query) {
            $query->where('type', 'supervisor');
        }])->orderBy('created_at', 'desc')->paginate(15);
        
        return view('admin.roles.index', compact('items'));
    }

    public function create()
    {
        $permissions = Permission::all();
        return view('admin.roles.create', compact('permissions'));
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
            return back()->withErrors($validator)->withInput();
        }

        $name = $request->name ?? Str::slug($request->name_en);

        $role = Role::create([
            'name' => $name,
            'name_ar' => $request->name_ar,
            'name_en' => $request->name_en,
            'description_ar' => $request->description_ar,
            'description_en' => $request->description_en,
            'guard_name' => 'web',
        ]);

        if ($request->has('permissions')) {
            $role->permissions()->sync($request->permissions);
        }

        return redirect()->route('admin.roles.index')->with('success', __('Role created successfully.'));
    }

    public function show($id)
    {
        $item = Role::with('permissions')->findOrFail($id);
        return view('admin.roles.show', compact('item'));
    }

    public function edit($id)
    {
        $item = Role::with('permissions')->findOrFail($id);
        $permissions = Permission::all();
        return view('admin.roles.edit', compact('item', 'permissions'));
    }

    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'description_ar' => 'nullable|string',
            'description_en' => 'nullable|string',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $role->update([
            'name_ar' => $request->name_ar,
            'name_en' => $request->name_en,
            'description_ar' => $request->description_ar,
            'description_en' => $request->description_en,
        ]);

        if ($request->has('permissions')) {
            $role->permissions()->sync($request->permissions);
        }

        return redirect()->route('admin.roles.index')->with('success', __('Role updated successfully.'));
    }

    public function destroy($id)
    {
        $role = Role::findOrFail($id);
        $role->delete();
        return redirect()->route('admin.roles.index')->with('success', __('Role deleted successfully.'));
    }

    public function download()
    {
        $roles = Role::withCount(['users as supervisors_count' => function ($query) {
            $query->where('type', 'supervisor');
        }])->get();

        $filename = "roles_" . date('Y-m-d_H-i-s') . ".csv";
        $handle = fopen('php://memory', 'w');
        
        fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM
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
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => "attachment; filename=\"$filename\"",
            ]
        );
    }
}
