<?php

return [
    'api_key' => env('CLOUDCONVERT_API_KEY', ''),

    // Use the CloudConvert Sanbox API (Defaults to false, which enables the Production API).
    'sandbox' => false,

    // You can find the secret used at the webhook settings: https://cloudconvert.com/dashboard/api/v2/webhooks
    'webhook_signing_secret' => env('CLOUDCONVERT_WEBHOOK_SIGNING_SECRET', ''),
];
