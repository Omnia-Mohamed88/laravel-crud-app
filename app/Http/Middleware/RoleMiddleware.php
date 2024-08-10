<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle($request, Closure $next, ...$roles)
    {
        $user = Auth::user();
    
        Log::info('Entering RoleMiddleware');
        Log::info('User roles:', ['roles' => $user ? $user->roles->pluck('name') : 'No user']);
        Log::info('Required roles:', $roles);
    
        if (!$user || !$user->hasAnyRole($roles)) {
            Log::warning('Access denied for user ID: ' . ($user ? $user->id : 'No user'));
            return response()->json(['message' => 'Forbidden'], 403);
        }
    
        Log::info('Access granted for user ID: ' . ($user ? $user->id : 'No user'));
        return $next($request);
    }
    
}    