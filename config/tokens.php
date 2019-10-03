<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Access Token
    |--------------------------------------------------------------------------
    |
    | The configuration of the 'access_token'
    |
    */
    'access_token' => [
        'algorithm' => 'RS256',         // Indicates which algorithm is used to sign and encrypt the JWT
        'iss' => env('APP_URL'),        // Indicates who issued the JWT
        'ttl' => 60 * 15,               // Indicates how long the token is valid
        'public_key' => str_replace(    // Indicates the JWT public key
            '||||',
            PHP_EOL,
            env('JWT_PUBLIC_KEY')
        ),
        'private_key' => str_replace(   // Indicates the JWT private key
            '||||',
            PHP_EOL,
            env('JWT_PRIVATE_KEY')
        ),
    ],

    /*
    |--------------------------------------------------------------------------
    | Refresh Token
    |--------------------------------------------------------------------------
    |
    | The configuration of the 'refresh_token'
    |
    */
    'refresh_token' => [
        'length' => 256,    // Indicates the amount of characters the token contains
        'ttl' => 60 * 15,   // Indicates how long the token is valid
    ],

    /*
    |--------------------------------------------------------------------------
    | Verify Mail Token
    |--------------------------------------------------------------------------
    |
    | The configuration of the 'verify_mail_token'
    |
    */
    'verify_mail_token' => [
        'length' => 128,     // Indicates the amount of characters the token contains
    ],

    /*
    |--------------------------------------------------------------------------
    | Reset Password Token
    |--------------------------------------------------------------------------
    |
    | The configuration of the 'reset_password_token'
    |
    */
    'reset_password_token' => [
        'length' => 256,     // Indicates the amount of characters the token contains
    ],
];