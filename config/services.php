<?php

return [
    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_REGION_SES'),
    ],

    's3' => [
        'bucket' => env('AWS_BUCKET'),
        'url_ttl' => 5,
    ],
];
