<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class JSONMiddleware
{
    /**
     * If POST,PUT,PATCH requests contains JSON interpret it
     * Also validate that the provided JSON is valid.
     *
     * @param Request $request
     * @param Closure $next
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (in_array($request->method(), ['POST', 'PUT', 'PATCH'])) {
            if (!$request->isJson()) {
                return response()->json(['error' => 'Body should be a JSON object'], 400);
            }

            json_decode($request->getContent());

            if ((JSON_ERROR_NONE !== json_last_error())) {
                return response()->json(['error' => 'Problems parsing JSON'], 400);
            }

            $data = $request->json()->all();
            $request->request->replace(is_array($data) ? $data : []);
        }

        return $next($request);
    }
}
