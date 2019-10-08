<?php

namespace App\Http\Middleware;

use App\Helpers\JWTHelper;
use App\Models\Session;
use App\Models\User;
use Closure;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class JWTMiddleware
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
            $credentials = JWTHelper::decode($access_token, 'auth');
        } catch (Exception $error) {
            return response()->json(
                ['error' => $error->getMessage()],
                'CLIENT_ERROR_UNAUTHORIZED'
            );
        }

        $request->refresh_session = Session::findOrFail($credentials->session_uuid);
        $request->user = User::findOrFail($credentials->sub);

        return $next($request);
    }
}
