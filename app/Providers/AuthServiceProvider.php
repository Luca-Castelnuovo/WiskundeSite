<?php

namespace App\Providers;

use App\User;
use Exception;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register()
    {
    }

    /**
     * Boot the authentication services for the application.
     */
    public function boot()
    {
        // Here you may define how you wish users to be authenticated for your Lumen
        // application. The callback which receives the incoming request instance
        // should return either a User instance or null. You're free to obtain
        // the User instance via an API token or any other method necessary.

        $this->app['auth']->viaRequest('api', function ($request) {
            $authorization_header = $request->headers->get('Authorization');
            $access_token = Str::replaceFirst('Bearer ', '', $authorization_header);

            try {
                $credentials = JWTHelper::decode($access_token, 'auth');
            } catch (Exception $e) {
                return null;
            }

            $request->session_uuid = $credentials->session_uuid;
            $request->user_id = $credentials->sub;

            $user = User::findOrFail($credentials->sub);

            // Todo: check if active
            if ($user->verify_email_token) {
                return false;
            }

            return $user;
        });
    }
}
