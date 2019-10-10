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
     * @param string  $required_role
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $required_role)
    {
        if ($request->role !== $required_role) {
            // code...
        }
        // TODO: implement authorization control
        // roles: student, teacher, admin, *

        /*
            $router->group(['middleware' => 'authorization', 'level' => admin], function () use ($router) {
                $router->post('subjects', 'SubjectsController@create');
                $router->put('subjects/{id:[0-9]+}', 'SubjectsController@update');
                $router->delete('subjects/{id:[0-9]+}', 'SubjectsController@delete');
            }
        */

        return $next($request);
    }
}
