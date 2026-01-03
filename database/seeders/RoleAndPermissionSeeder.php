<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Database\Seeder;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define Modules
        $modules = [
            'individual customers',
            'corporate customers',
            'technicians',
            'maintenance companies',
            'orders',
            'services',
            'cities and districts',
            'supervisors',
            'roles and permissions',
            'financial reports',
            'settings',
            'contents',
            'faqs',
            'social links',
            'terms and conditions',
            'notifications',
            'inquiry and support',
        ];

        // Define Actions
        $actions = [
            'view',
            'add',
            'edit',
            'delete',
            'block',
            'deactivate',
            'activate',
            'download',
        ];

        foreach ($modules as $module) {
            foreach ($actions as $action) {
                Permission::firstOrCreate(['name' => "{$action} {$module}", 'guard_name' => 'api']);
            }
        }

        // Define Roles and Assign Permissions
        
        // 1. Super Admin (All permissions)
        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin'], [
            'guard_name' => 'api',
            'name_ar' => 'مدير النظام',
            'name_en' => 'Super Admin',
            'description_ar' => 'صلاحيات كاملة للنظام',
            'description_en' => 'Full system access',
        ]);
        $superAdmin->permissions()->sync(Permission::all());

        // 2. Client Manager
        $clientManager = Role::firstOrCreate(['name' => 'Client Manager'], [
            'guard_name' => 'api',
            'name_ar' => 'مدير العملاء',
            'name_en' => 'Client Manager',
            'description_ar' => 'إدارة العملاء والطلبات',
            'description_en' => 'Manage customers and orders',
        ]);
        $clientManager->permissions()->sync(
            Permission::where(function($q) {
                $q->where('name', 'like', '%individual customers')
                  ->orWhere('name', 'like', '%corporate customers')
                  ->orWhere('name', 'like', '%orders');
            })->get()
        );

        // 3. Technical Manager
        $techManager = Role::firstOrCreate(['name' => 'Technical Manager'], [
            'guard_name' => 'api',
            'name_ar' => 'مدير فني',
            'name_en' => 'Technical Manager',
            'description_ar' => 'إدارة الفنيين والخدمات',
            'description_en' => 'Manage technicians and services',
        ]);
        $techManager->permissions()->sync(
            Permission::where(function($q) {
                $q->where('name', 'like', '%technicians')
                  ->orWhere('name', 'like', '%maintenance companies')
                  ->orWhere('name', 'like', '%services');
            })->get()
        );

        // 4. Financial Auditor
        $auditor = Role::firstOrCreate(['name' => 'Financial Auditor'], [
            'guard_name' => 'api',
            'name_ar' => 'مدقق مالي',
            'name_en' => 'Financial Auditor',
            'description_ar' => 'عرض التقارير المالية',
            'description_en' => 'View financial reports',
        ]);
        $auditor->permissions()->sync(
            Permission::where('name', 'like', '%financial reports')->get()
        );

        // 5. Content Manager
        $contentManager = Role::firstOrCreate(['name' => 'Content Manager'], [
            'guard_name' => 'api',
            'name_ar' => 'مدير المحتوى',
            'name_en' => 'Content Manager',
            'description_ar' => 'إدارة المحتوى والمقالات والأسئلة الشائعة',
            'description_en' => 'Manage content, FAQs, and articles',
        ]);
        $contentManager->permissions()->sync(
            Permission::where(function($q) {
                $q->where('name', 'like', '%contents')
                  ->orWhere('name', 'like', '%faqs')
                  ->orWhere('name', 'like', '%terms and conditions')
                  ->orWhere('name', 'like', '%social links');
            })->get()
        );

        // 6. Support Agent
        $supportAgent = Role::firstOrCreate(['name' => 'Support Agent'], [
            'guard_name' => 'api',
            'name_ar' => 'موظف دعم',
            'name_en' => 'Support Agent',
            'description_ar' => 'إدارة الاستفسارات والبلاغات',
            'description_en' => 'Manage inquiries and support tickets',
        ]);
        $supportAgent->permissions()->sync(
            Permission::where(function($q) {
                $q->where('name', 'like', '%inquiry and support')
                  ->orWhere('name', 'like', '%notifications');
            })->get()
        );
    }
}
