<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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
        $roles = explode(',', $roles);

        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $userRoles = $user->roles->pluck('name')->toArray();

        Log::info('User Roles: ' . json_encode($userRoles)); 
        Log::info('Required Roles: ' . json_encode($roles));

        if ($user->hasRole('superadmin') || array_intersect($roles, $userRoles)) {
            return $next($request);
        }

        return response()->json(['error' => 'Forbidden'], 403);
    }
}
