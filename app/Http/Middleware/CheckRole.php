<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $roles
     * @return mixed
     */
    public function handle($request, Closure $next, $roles)
    {
        // Split roles by comma
        $roles = explode(',', $roles);

        // Retrieve the authenticated user's role
        $userRole = Auth::check() ? Auth::user()->role : null;

        // Allow access if user role is superadmin or matches one of the required roles
        if ($userRole === 'superadmin' || in_array($userRole, $roles)) {
            return $next($request);
        }

        // If access is denied
        return response()->json(['error' => 'Forbidden'], 403);
    }
}
