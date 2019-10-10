<?php

return [
    'private_key' => env('RECAPTCHA_PRIVATE_KEY'),
    'public_key' => env('RECAPTCHA_PUBLIC_KEY'),
    'endpoint' => 'https://www.google.com/recaptcha/api/siteverify',
];
