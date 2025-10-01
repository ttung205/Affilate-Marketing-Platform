<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        
        if (!in_array($user->role, $roles)) {
            // Instead of aborting, redirect to appropriate dashboard
            switch ($user->role) {
                case 'admin':
                    return redirect()->route('admin.dashboard');
                case 'publisher':
                    return redirect()->route('publisher.dashboard');
                case 'shop':
                    return redirect()->route('shop.dashboard');
                default:
                    return redirect()->route('home');
            }
        }

        return $next($request);
    }
}

