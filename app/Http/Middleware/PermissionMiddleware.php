<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use App\Models\RoleAndPermission;

class PermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $permission
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, $permission): Response
    {
        $user = Auth::guard('admin')->user();

        if (!$user) {
            abort(403, 'Unauthorized');
        }

        // Get the role's permissions from the DB
        $role = RoleAndPermission::where('role_name', $user->role)->first();

        $permissions = [];
        if ($role && $role->permissions) {
            $permissions = is_array($role->permissions)
                ? $role->permissions
                : json_decode($role->permissions, true);
        }

        // Accept both 'dashboard.view' and 'view_dashboard' style permissions
        $permissionVariants = [$permission, str_replace('.', '_', $permission), str_replace('_', '.', $permission)];

        $hasPermission = false;
        foreach ($permissionVariants as $perm) {
            if (in_array($perm, $permissions ?? [])) {
                $hasPermission = true;
                break;
            }
        }

        if (!$hasPermission) {
            abort(403, 'Unauthorized');
        }

        return $next($request);
    }
}
