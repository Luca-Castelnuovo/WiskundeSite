<?php

namespace App\Http\Middleware;

use App\Helpers\JWTHelper;
use Closure;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AuthenticationMiddleware
{
    /**
     * Validate JWT token.
     *
     * @param Request $request
     * @param Closure $next
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $authorization_header = $request->headers->get('Authorization');
        $access_token = Str::replaceFirst('Bearer ', '', $authorization_header);

        try {
            $credentials = JWTHelper::decode($access_token, 'access');
        } catch (Exception $error) {
            return response()->json(['error' => $error->getMessage()], 401);
        }

        $request->session_uuid = $credentials->token;
        $request->user_id = $credentials->sub;
        $request->role = $credentials->role;

        return $next($request);
    }
}
