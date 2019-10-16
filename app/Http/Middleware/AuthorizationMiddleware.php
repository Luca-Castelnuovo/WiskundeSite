<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AuthorizationMiddleware
{
    /**
     * Validate JWT token.
     *
     * @param Request $request
     * @param Closure $next
     * @param string  $authorized_roles
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $authorized_roles)
    {
        $authorized_roles = explode('.', $authorized_roles);

        if (!in_array($request->role, $authorized_roles)) {
            return response()->json(['error' => 'incorrect access level'], 401);
        }

        return $next($request);
    }
}
