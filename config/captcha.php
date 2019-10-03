<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Private Key
    |--------------------------------------------------------------------------
    |
    | The private key to send to google to verify the response
    |
    */

    'private_key' => env('RECAPTCHA_PRIVATE_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Public Key
    |--------------------------------------------------------------------------
    |
    | The public key to request a recaptcha box
    |
    */

    'public_key' => env('RECAPTCHA_PUBLIC_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Endpoint
    |--------------------------------------------------------------------------
    |
    | The url to send the verification request
    |
    */

    'endpoint' => 'https://www.google.com/recaptcha/api/siteverify'
];