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
    $router->post('orders/webhook', [
        'as' => 'mollie_webhook', 'uses' => 'OrderController@webhook',
    ]);
});

$router->group(['middleware' => 'authentication'], function () use ($router) {
    // Auth
    $router->post('auth/logout', 'AuthController@logout');

    // Account
    $router->get('account', 'AccountsController@show');
    $router->put('account', 'AccountsController@update');
    $router->delete('account', 'AccountsController@delete');
    $router->get('account/products', 'AccountsController@showProducts');
    $router->get('account/orders', 'AccountsController@showOrders');
    $router->get('account/sessions', 'AccountsController@showSessions');
    $router->delete('account/sessions', 'AccountsController@revokeSession');

    // Products
    $router->get('products', 'ProductsController@all');
    $router->get('products/{id:[0-9]+}', 'ProductsController@show');
    $router->get('products/{id:[0-9]+}/open', 'ProductsController@open');
    $router->post('products/format', 'ProductsController@format');
    $router->post('products', 'ProductsController@create');
    $router->put('products/{id:[0-9]+}', 'ProductsController@update');
    $router->delete('products/{id:[0-9]+}', 'ProductsController@delete');

    // Order
    $router->get('orders/{id:[0-9a-fA-F]{8}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{12}}', 'OrderController@show');
    $router->post('orders', 'OrderController@create');

    // Admin
    $router->get('admin/users', 'AdminController@all');
    $router->get('admin/users/{id:[0-9]+}', 'AdminController@show');
    $router->put('admin/users/{id:[0-9]+}', 'AdminController@update');
    $router->delete('admin/users/{id:[0-9]+}', 'AdminController@delete');
    $router->get('admin/users/{id:[0-9]+}/products', 'AdminController@showProducts');
    $router->get('admin/users/{id:[0-9]+}/orders', 'AdminController@showOrders');
    $router->get('admin/users/{id:[0-9]+}/sessions', 'AdminController@showSessions');
    $router->delete('admin/users/{id:[0-9]+}/sessions', 'AdminController@revokeSession');
});
