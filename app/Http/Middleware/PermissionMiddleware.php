<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PermissionMiddleware
{
    public function handle(Request $request, Closure $next, $permission)
    {
      
        if (Auth::guard('admin')->check()) {
            return $next($request); 
        }

      
        if (Auth::guard('web')->check()) { 
            
            $user = Auth::guard('web')->user();

            if (!$user->hasPermission($permission)) {
        
                return redirect()->back()->withErrors('You do not have access');
            }

            return $next($request);
        }


        return redirect()->route('login')->withErrors('You must be logged in.');
    }
}
