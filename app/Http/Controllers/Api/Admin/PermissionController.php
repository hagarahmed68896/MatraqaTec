<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PermissionController extends Controller
{
    public function index()
    {
        $permissions = Permission::all();
        return response()->json(['status' => true, 'message' => 'Permissions retrieved', 'data' => $permissions]);
    }

    public function grouped()
    {
        $permissions = Permission::all();
        $grouped = [];

        // Mapping internal names to Arabic and English labels
        $actionLabels = [
            'view' => ['ar' => 'عرض', 'en' => 'View'],
            'add' => ['ar' => 'إضافة', 'en' => 'Add'],
            'edit' => ['ar' => 'تعديل', 'en' => 'Edit'],
            'delete' => ['ar' => 'حذف', 'en' => 'Delete'],
            'block' => ['ar' => 'حظر', 'en' => 'Block'],
            'deactivate' => ['ar' => 'الإيقاف', 'en' => 'Deactivate'],
            'activate' => ['ar' => 'تفعيل', 'en' => 'Activate'],
            'download' => ['ar' => 'تحميل', 'en' => 'Download'],
        ];

        $moduleLabels = [
            'individual customers' => ['ar' => 'العملاء (أفراد)', 'en' => 'Individual Customers'],
            'corporate customers' => ['ar' => 'العملاء (شركات)', 'en' => 'Corporate Customers'],
            'technicians' => ['ar' => 'الفنيون', 'en' => 'Technicians'],
            'maintenance companies' => ['ar' => 'شركات الصيانة', 'en' => 'Maintenance Companies'],
            'orders' => ['ar' => 'إدارة الطلبات', 'en' => 'Order Management'],
            'services' => ['ar' => 'إدارة الخدمات', 'en' => 'Service Management'],
            'cities and districts' => ['ar' => 'إدارة المدن والمناطق', 'en' => 'Cities and Districts'],
            'supervisors' => ['ar' => 'إدارة المشرفين', 'en' => 'Supervisor Management'],
            'roles and permissions' => ['ar' => 'الصلاحيات', 'en' => 'Roles and Permissions'],
            'financial reports' => ['ar' => 'التقارير المالية', 'en' => 'Financial Reports'],
            'settings' => ['ar' => 'الإعدادات', 'en' => 'Settings'],
            'contents' => ['ar' => 'إدارة المحتوى', 'en' => 'Content Management'],
            'faqs' => ['ar' => 'الأسئلة الشائعة', 'en' => 'FAQs'],
            'social links' => ['ar' => 'روابط التواصل', 'en' => 'Social Links'],
            'terms and conditions' => ['ar' => 'الشروط والأحكام', 'en' => 'Terms and Conditions'],
            'notifications' => ['ar' => 'التنبيهات', 'en' => 'Notifications'],
            'inquiry and support' => ['ar' => 'الاستفسارات والدعم', 'en' => 'Inquiry and Support'],
        ];

        foreach ($permissions as $permission) {
            $parts = explode(' ', $permission->name, 2);
            if (count($parts) === 2) {
                $action = $parts[0];
                $module = $parts[1];

                if (!isset($grouped[$module])) {
                    $grouped[$module] = [
                        'label_ar' => $moduleLabels[$module]['ar'] ?? ucfirst($module),
                        'label_en' => $moduleLabels[$module]['en'] ?? ucfirst($module),
                        'actions' => []
                    ];
                }

                $grouped[$module]['actions'][$action] = [
                    'id' => $permission->id,
                    'label_ar' => $actionLabels[$action]['ar'] ?? ucfirst($action),
                    'label_en' => $actionLabels[$action]['en'] ?? ucfirst($action),
                    'name' => $permission->name
                ];
            }
        }

        return response()->json([
            'status' => true, 
            'message' => 'Grouped permissions retrieved', 
            'data' => [
                'permissions' => $grouped,
                'action_order' => ['view', 'add', 'edit', 'delete', 'block', 'deactivate', 'activate', 'download']
            ]
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:permissions,name',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first(), 'errors' => $validator->errors()], 422);
        }

        $permission = Permission::create([
            'name' => $request->name,
            'guard_name' => 'api',
        ]);

        return response()->json(['status' => true, 'message' => 'Permission created successfully', 'data' => $permission]);
    }

    public function show($id)
    {
        $permission = Permission::find($id);
        if (!$permission) return response()->json(['status' => false, 'message' => 'Permission not found'], 404);
        return response()->json(['status' => true, 'message' => 'Permission retrieved', 'data' => $permission]);
    }

    public function update(Request $request, $id)
    {
        $permission = Permission::find($id);
        if (!$permission) return response()->json(['status' => false, 'message' => 'Permission not found'], 404);

        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|unique:permissions,name,' . $id,
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first(), 'errors' => $validator->errors()], 422);
        }

        if ($request->has('name')) $permission->name = $request->name;
        $permission->save();

        return response()->json(['status' => true, 'message' => 'Permission updated', 'data' => $permission]);
    }

    public function destroy($id)
    {
        $permission = Permission::find($id);
        if (!$permission) return response()->json(['status' => false, 'message' => 'Permission not found'], 404);

        $permission->delete();
        return response()->json(['status' => true, 'message' => 'Permission deleted successfully']);
    }
}
