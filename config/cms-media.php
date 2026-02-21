<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Image Proxy (imgproxy)
    |--------------------------------------------------------------------------
    */

    'proxy' => [
        'enabled' => (bool) env('IMGPROXY_ENABLE', false),
        'url' => env('IMGPROXY_URL', ''),
        'key' => env('IMGPROXY_KEY', ''),
        'salt' => env('IMGPROXY_SALT', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | Unsplash Integration
    |--------------------------------------------------------------------------
    */

    'unsplash' => [
        'enabled' => (bool) env('UNSPLASH_APP_KEY'),
        'app_id' => env('UNSPLASH_APP_ID', ''),
        'access_key' => env('UNSPLASH_APP_KEY', ''),
        'secret_key' => env('UNSPLASH_SECRET_KEY', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | Media Settings
    |--------------------------------------------------------------------------
    */

    'media' => [
        'max_upload_size' => 10240,
        'accepted_types' => [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
            'application/pdf',
        ],
    ],

];
