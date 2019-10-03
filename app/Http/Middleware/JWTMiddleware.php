<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;
use App\Helpers\AuthHelper;
use Illuminate\Http\Request;

class JWTMiddleware {
    /**
     * Validate JWT token
     *
     * @param Request   $request
     * @param Closure   $next
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $access_token = AuthHelper::parseAuthHeader($request->headers->get('Authorization'));
        $credentials = AuthHelper::validateAccessToken($access_token, $request->ip());

        // Returns an error message for an invalid token
        if (isset($credentials->error)) {
            $http_code = $credentials->http;
            unset($credentials->http);
            return response()->json($credentials, $http_code);
        }

        // Put the user_id in the request
        $request->user_id = $credentials->sub;

        // Put the session_uuid in the request
        $request->session_uuid = $credentials->session_uuid;

        return $next($request);
    }
}