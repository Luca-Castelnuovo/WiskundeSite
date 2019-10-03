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

/**
 * Factory definition for model App\Models\User.
 */
$factory->define(App\Models\User::class, function ($faker) {
    return [
        'name' => $faker->name,
        'email' => $faker->unique()->safeEmail,
        'password' => app('hash')->make('test'),

        //'address' => $faker->address
    ];
});

/**
 * Factory definition for model App\Models\Product.
 */
$factory->define(App\Models\Product::class, function ($faker) {
    return [
        'name' => $faker->unique()->text(16),
        'img_url' => $faker->imageUrl(),
        'price' => $faker->randomFloat(2, 0.10, 3.50),
        'is_healthy' => $faker->boolean(),
        'tags' => [$faker->numberBetween(0, 6), $faker->numberBetween(0, 6)],
        'recommended_addons' => [$faker->numberBetween(0,25), $faker->numberBetween(0,25)],
    ];
});