<?php

return [
    'header' => env('INTERNAL_ACCESS_HEADER', 'X-Internal-Token'),

    'tokens' => [
        'default' => env('INTERNAL_ACCESS_TOKEN'),
        'maintenance' => env('MAINTENANCE_ACCESS_TOKEN', env('INTERNAL_ACCESS_TOKEN')),
        'cron' => env('CRON_ACCESS_TOKEN', env('INTERNAL_ACCESS_TOKEN')),
    ],

    'verify_ssl' => env('CURL_VERIFY_SSL', true),

    'placeholder_image' => [
        'max_dimension' => env('PLACEHOLDER_IMAGE_MAX_DIMENSION', 2000),
        'max_pixels' => env('PLACEHOLDER_IMAGE_MAX_PIXELS', 4000000),
        'min_dimension' => env('PLACEHOLDER_IMAGE_MIN_DIMENSION', 16),
    ],
];
