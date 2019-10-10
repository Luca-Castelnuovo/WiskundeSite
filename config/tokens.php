<?php

return [
    'jwt_token' => [
        'algorithm' => 'RS256', // Indicates which algorithm is used to sign and encrypt the JWT
        'iss' => env('APP_URL'), // Indicates who issued the JWT
        'public_key' => str_replace('||||', PHP_EOL, env('JWT_PUBLIC_KEY')), // Indicates the JWT public key
        'private_key' => str_replace('||||', PHP_EOL, env('JWT_PRIVATE_KEY')), // Indicates the JWT private key
    ],

    'access_token' => [
        'ttl' => 900, // 15 minutes
    ],

    'refresh_token' => [
        'ttl' => 2592000, // 30 days
    ],

    'verify_mail_token' => [
        'length' => 64, // character amount
        'ttl' => 172800, // 48 hour
    ],

    'reset_password_token' => [
        'length' => 64, // character amount
        'ttl' => 3600, // 1 hour
    ],
];
