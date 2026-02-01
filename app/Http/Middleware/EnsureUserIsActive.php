<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use App\Models\User;

class EnsureUserIsActive
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // If no authenticated user, nothing to do here.
        $user = Auth::user();
        if (!$user) {
            return $next($request);
        }

        // If the authenticated model is the User model, check is_active column if present.
        if ($user instanceof User && Schema::hasColumn($user->getTable(), 'is_active')) {
            if ($user->is_active === false) {
                Auth::logout();
                return redirect()->route('superadmin.login')->withErrors(['email' => 'Your account is deactivated. Contact administrator.']);
            }
        }

        // Also, prevent operations on deactivated target users passed as route parameters.
        foreach ($request->route()?->parameters() ?? [] as $param) {
            if ($param instanceof User && Schema::hasColumn($param->getTable(), 'is_active') && $param->is_active === false) {
                abort(403, 'Operation not allowed on a deactivated user.');
            }
        }

        return $next($request);
    }
}
