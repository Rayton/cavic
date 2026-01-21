<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated {
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next, ...$guards) {
        if (Auth::check()) {
            $user = Auth::user();
            // Redirect super admins to admin dashboard
            if (is_null($user->tenant_id) || $user->user_type == 'superadmin') {
                return redirect()->route('admin.dashboard.index');
            }
            // For tenant users, redirect to their tenant dashboard
            if ($user->tenant && $user->tenant->slug) {
                if($request->tenant == null || !app()->bound('tenant')){
                    return redirect()->route('dashboard.index', ['tenant' => $user->tenant->slug]);
                }
                return redirect()->route('dashboard.index');
            }
        }
        return $next($request);
    }
}
