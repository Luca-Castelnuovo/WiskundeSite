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

// Ratelimit (60 requests / 1 minute)
$router->group(['middleware' => 'ratelimit:60,60'], function () use ($router) {
    // General
    $router->get('/', 'GeneralController@index');

    // Auth
    $router->post('auth/register', 'AuthController@register');
    $router->post('auth/verify', 'AuthController@verifyEmail');
    $router->post('auth/login', 'AuthController@login');
    $router->post('auth/refresh', 'AuthController@refresh');
    $router->post('auth/reset/request', 'AuthController@requestResetPassword');
    $router->post('auth/reset', 'AuthController@resetPassword');

    // Order
    $router->post('order/webhook', [
        'as' => 'mollie_webhook', 'uses' => 'OrderController@webhook',
    ]);
});

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
    $router->get('products/{id:[0-9]+}', 'ProductsController@show');
    $router->get('products/{id:[0-9]+}/open', 'ProductsController@open');
    $router->group(['middleware' => 'authorization:teacher.admin'], function () use ($router) {
        $router->post('products', 'ProductsController@create');
        $router->put('products/{id:[0-9]+}', 'ProductsController@update');
        $router->delete('products/{id:[0-9]+}', 'ProductsController@delete');
    });

    // Order
    $router->group(['middleware' => 'authorization:student.teacher'], function () use ($router) {
        $router->get('order/{id:[0-9a-fA-F]{8}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{12}}', 'OrderController@status');
        $router->post('order', 'OrderController@create');
    });

    // Filter
    // $router->get('filter', 'FilterController@index');

    // Admin
    $router->group(['middleware' => 'authorization:admin'], function () use ($router) {
        // TODO: implement admin endpoints
        // $router->get('admin/users', 'AccountsController@admin_all');
        // $router->get('admin/users/{id:[0-9]+}', 'AccountsController@admin_view');
        // $router->put('admin/users/{id:[0-9]+}', 'AccountsController@admin_update');
        // $router->delete('admin/users/{id:[0-9]+}', 'AccountsController@admin_delete');
    });
});
