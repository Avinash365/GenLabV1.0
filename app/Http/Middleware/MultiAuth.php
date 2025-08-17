<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class MultiAuth
{
    public function handle($request, Closure $next, ...$guards)
    {
        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                Auth::shouldUse($guard);  
                return $next($request);
            }
        }

        // If not authenticated
        if (in_array('admin', $guards)) {
            return redirect()->route('superadmin.login');
        }

        if (in_array('web', $guards)) {
            return redirect()->route('login');
        }

        return redirect()->route('login');
    }
}
