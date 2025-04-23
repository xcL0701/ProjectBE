<?php
    return [
        'paths' => ['api/*', 'sanctum/csrf-cookie', 'storage/*'],
        'allowed_methods' => ['*'],
        'allowed_origins' => ['https://ptcsi.vercel.app'],
        'allowed_headers' => ['*'],
        'exposed_headers' => [],
        'max_age' => 0,
        'supports_credentials' => true,
    ];

