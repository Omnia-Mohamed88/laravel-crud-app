<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class CheckPermission
{
    public function handle($request, Closure $next, $permission)
    {
        if (!Auth::check() || !Auth::user()->can($permission)) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        return $next($request);
    }
}
