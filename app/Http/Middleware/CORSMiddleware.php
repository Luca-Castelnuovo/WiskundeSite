<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CORSMiddleware {

    /**
     * Add CORS headers to requests
     *
     * @param Request $request
     * @param Closure $next
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $headers = [
            'Access-Control-Allow-Origin'  => implode(", ", config('CORS.allow_origins')),
            'Access-Control-Allow-Methods' => implode(", ", config('CORS.allow_methods')),
            'Access-Control-Allow-Headers' => implode(", ", config('CORS.allow_headers')),
        ];

        $response = $next($request);

        foreach ($headers as $key => $value) {
            $response->header($key, $value);
        }

        return $response;
    }
}
