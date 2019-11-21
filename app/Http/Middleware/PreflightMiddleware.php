<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class PreflightMiddleware {

    /**
     * Accept all preflight requests
     *
     * @param Request $request
     * @param Closure $next
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->isMethod('options')) {
            return response()->json(
                ['message' => 'Preflight Accepted'],
                200
            );
        }
        
        return $next($request);
    }
}
