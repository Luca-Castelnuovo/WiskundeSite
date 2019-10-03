<?php

namespace App\Http\Middleware;

use Closure;
use App\Helpers\HttpStatusCodes;
use Illuminate\Http\Request;

class JSONMiddleware {

    /**
     * If POST,PUT,PATCH requests contains JSON interpret it
     * Also validate that the provided JSON is valid
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
                return response()->json(
                    [
                        'error' => 'Body should be a JSON object'
                    ],
                    HttpStatusCodes::CLIENT_ERROR_BAD_REQUEST
                );
            }

            json_decode($request->getContent());

            if ((json_last_error() !== JSON_ERROR_NONE)) {
                return response()->json(
                    [
                        'error' => 'Problems parsing JSON'
                    ],
                    HttpStatusCodes::CLIENT_ERROR_BAD_REQUEST
                );
            }

            $data = $request->json()->all();
            $request->request->replace(is_array($data) ? $data : []);
        }

        return $next($request);
    }
}
