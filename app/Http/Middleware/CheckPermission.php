<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('admin.login');
        }

        // Super Admin Bypass
        if ($user->type === 'admin') {
            return $next($request);
        }

        $routeName = $request->route()->getName();
        if (!$routeName) {
            return $next($request);
        }

        $permission = $this->mapRouteToPermission($routeName);
        
        if ($permission && !$user->hasPermission($permission)) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status' => false,
                    'message' => __('عذراً، ليس لديك صلاحية للقيام بهذا الإجراء.')
                ], 403);
            }
            
            return redirect()->route('admin.dashboard')->with('error', __('عذراً، ليس لديك صلاحية للوصول لهذه الصفحة.'));
        }

        return $next($request);
    }

    /**
     * Maps route names to database permissions.
     */
    private function mapRouteToPermission($routeName)
    {
        $parts = explode('.', $routeName);
        if (count($parts) < 3 || $parts[0] !== 'admin') {
            return null;
        }

        $module = str_replace('-', ' ', $parts[1]);

        // Special Mappings
        $specialModules = [
            'roles' => 'roles and permissions',
            'terms' => 'terms and conditions',
            'privacy-policies' => 'privacy policies',
            'social-links' => 'social links',
            'financial-settlements' => 'financial reports',
            'maintenance-companies' => 'maintenance companies',
            'corporate-customers' => 'corporate customers',
            'individual-customers' => 'individual customers',
            'cities' => 'cities and districts',
            'privacy-policies' => 'privacy policies',
            'terms-and-conditions' => 'terms and conditions',
            'inquiry-and-support' => 'inquiry and support',
        ];

        if (isset($specialModules[$parts[1]])) {
            $module = $specialModules[$parts[1]];
        }

        $action = $parts[2];

        $map = [
            'index' => 'view',
            'show' => 'view',
            'create' => 'add',
            'store' => 'add',
            'edit' => 'edit',
            'update' => 'edit',
            'destroy' => 'delete',
            'bulk-destroy' => 'delete',
            'bulk-delete' => 'delete',
            'toggle-block' => 'block',
            'bulk-block' => 'block',
            'bulk-unblock' => 'activate', // Often unblock is considered activation or separate
            'change-status' => 'activate',
            'download' => 'download',
            'take-action' => 'edit',
            'accept' => 'activate',
            'refuse' => 'deactivate',
        ];

        if (isset($map[$action])) {
            return "{$map[$action]} {$module}";
        }

        return null;
    }
}
