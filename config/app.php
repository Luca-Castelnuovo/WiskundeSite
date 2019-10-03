<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Name
    |--------------------------------------------------------------------------
    |
    | The name is used in email signatures
    |
    */
    'name' => env('APP_NAME'),

    /*
    |--------------------------------------------------------------------------
    | Domain
    |--------------------------------------------------------------------------
    |
    | The domain is the root domain of the API
    |
    */
    'domain' => env('APP_DOMAIN'),

    /*
    |--------------------------------------------------------------------------
    | URL
    |--------------------------------------------------------------------------
    |
    | URL is the base domain of the API
    |
    */
    'url' => env('APP_URL'),

    /*
    |--------------------------------------------------------------------------
    | Mail
    |--------------------------------------------------------------------------
    |
    | Email for application messages
    |
    */
    'mail' => env('APP_MAIL'),


    /*
    |--------------------------------------------------------------------------
    | Timezone
    |--------------------------------------------------------------------------
    |
    | Specifies for Carbon
    |
    */
    'timezone' => env('APP_TIMEZONE'),

    /*
    |--------------------------------------------------------------------------
    | Debug
    |--------------------------------------------------------------------------
    |
    | Certain checks are disabled in debug mode
    |
    */
    'debug' => env('APP_DEBUG'),
];