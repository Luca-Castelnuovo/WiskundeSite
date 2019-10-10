<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

/**
 * General.
 */
$router->get('/', 'GeneralController@index');

// Auth
$router->post('auth/register', 'AuthController@register');
$router->post('auth/verify', 'AuthController@verifyEmail');
$router->post('auth/login', 'AuthController@login');
$router->post('auth/refresh', 'AuthController@refresh');
$router->post('auth/reset/request', 'AuthController@requestResetPassword');
$router->post('auth/reset', 'AuthController@resetPassword');

$router->group(['middleware' => 'authentication'], function () use ($router) {
    // Auth
    $router->post('auth/logout', 'AuthController@logout');

    // Account
    $router->get('account', 'AccountsController@index');
    $router->put('account', 'AccountsController@update');
    $router->delete('account', 'AccountsController@delete');
    $router->get('account/sessions', 'AccountsController@showSessions');
    $router->delete('account/sessions', 'AccountsController@revoke');

    // Products
    $router->get('products', 'ProductsController@index');
    $router->post('products', 'ProductsController@create');
    $router->group(['prefix' => 'products/{id:[0-9,]+}'], function ($router) {
        $router->get('/', 'ProductsController@show');
        $router->put('/', 'ProductsController@update');
        $router->delete('/', 'ProductsController@delete');
    });

    // Subjects
    $router->get('subjects', 'SubjectsController@index');
    $router->post('subjects', 'SubjectsController@create');
    $router->group(['prefix' => 'subjects/{id:[0-9,]+}'], function ($router) {
        $router->get('/', 'SubjectsController@show');
        $router->put('/', 'SubjectsController@update');
        $router->delete('/', 'SubjectsController@delete');

        $router->get('/products', 'SubjectsController@showProducts');
    });

    // Order
    $router->post('order', 'OrderController@create');
});
