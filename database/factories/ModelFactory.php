<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

// Factory definition for model App\Models\User.
$factory->define(App\Models\User::class, function ($faker) {
    return [
        'name' => 'fooBar',
        'email' => 'ltcastelnuovo@gmail.com',
        'password' => app('hash')->make('fooBar1234'),
        'role' => '*',
    ];
});
