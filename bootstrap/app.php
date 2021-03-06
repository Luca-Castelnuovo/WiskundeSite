<?php

require_once __DIR__.'/../vendor/autoload.php';

(new Laravel\Lumen\Bootstrap\LoadEnvironmentVariables(
    dirname(__DIR__)
))->bootstrap();

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| Here we will load the environment and create the application instance
| that serves as the central piece of this framework. We'll use this
| application as an "IoC" container and router for this framework.
|
*/

$app = new Laravel\Lumen\Application(
    dirname(__DIR__)
);

$app->withFacades();

$app->withEloquent();

/*
|--------------------------------------------------------------------------
| Load Configuration Files
|--------------------------------------------------------------------------
|
| Here, we will load the configuration files.
|
*/

$app->configure('app');
$app->configure('captcha');
$app->configure('cloudconvert');
$app->configure('CORS');
$app->configure('insights');
$app->configure('mail');
$app->configure('mollie');
$app->configure('services');
$app->configure('tokens');

/*
|--------------------------------------------------------------------------
| Set aliases
|--------------------------------------------------------------------------
|
| Here, the aliases are set.
|
*/

$app->alias('mailer', Illuminate\Mail\Mailer::class);
$app->alias('mailer', Illuminate\Contracts\Mail\Mailer::class);
$app->alias('mailer', Illuminate\Contracts\Mail\MailQueue::class);

/*
|--------------------------------------------------------------------------
| Register Container Bindings
|--------------------------------------------------------------------------
|
| Now we will register a few bindings in the service container. We will
| register the exception handler and the console kernel. You may add
| your own bindings here if you like or you can make another file.
|
*/

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

/*
|--------------------------------------------------------------------------
| Register Middleware
|--------------------------------------------------------------------------
|
| Next, we will register the middleware with the application. These can
| be global middleware that run before and after each request into a
| route or middleware that'll be assigned to some specific routes.
|
*/

$app->middleware([
    App\Http\Middleware\CORSMiddleware::class,
    App\Http\Middleware\PreflightMiddleware::class,
    App\Http\Middleware\JSONMiddleware::class,
]);

$app->routeMiddleware([
    'authentication' => App\Http\Middleware\AuthenticationMiddleware::class,
    'authorization' => App\Http\Middleware\AuthorizationMiddleware::class,
    'ratelimit' => App\Http\Middleware\RatelimitMiddleware::class,
]);

/*
|--------------------------------------------------------------------------
| Register Service Providers
|--------------------------------------------------------------------------
|
| Here we will register all of the application's service providers which
| are used to bind services into the container. Service providers are
| totally optional, so you are not required to uncomment this line.
|
*/

$app->register(Aws\Laravel\AwsServiceProvider::class);
$app->register(Illuminate\Mail\MailServiceProvider::class);
$app->register(Mollie\Laravel\MollieServiceProvider::class);
$app->register(\NunoMaduro\PhpInsights\Application\Adapters\Laravel\InsightsServiceProvider::class);

/*
|--------------------------------------------------------------------------
| Load The Application Routes
|--------------------------------------------------------------------------
|
| Next we will include the routes file so that they can all be added to
| the application. This will provide all of the URLs the application
| can respond to, as well as the controllers that may handle them.
|
*/

$app->router->group([
    'namespace' => 'App\Http\Controllers',
], function ($router) {
    require __DIR__.'/../routes/web.php';
});

return $app;
