<?php

return [
    'jwt_token' => [
        'algorithm' => 'RS256',
        'iss' => env('APP_URL'),
        'length' => 64,
        'public_key' => str_replace('||||', PHP_EOL, env('JWT_PUBLIC_KEY')),
        'private_key' => str_replace('||||', PHP_EOL, env('JWT_PRIVATE_KEY')),
    ],

    'access_token' => [
        'ttl' => 900, // 15 minutes
    ],

    'refresh_token' => [
        'ttl' => 2592000, // 30 days
    ],

    'verify_email_token' => [
        'ttl' => 172800, // 48 hour
    ],

    'reset_password_token' => [
        'ttl' => 3600, // 1 hour
    ],
];
