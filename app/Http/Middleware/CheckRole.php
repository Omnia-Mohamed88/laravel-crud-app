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

        $userRole = Auth::check() ? Auth::user()->role : null; 

        if ($userRole === 'superadmin' || in_array($userRole, $roles)) {
            return $next($request);
        }

        return response()->json(['error' => 'Forbidden'], 403);
    }
}
