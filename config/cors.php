<?php

$productionOrigins = array_values(array_filter([
    env('FRONTEND_URL'),
    'https://umairfabrics.com',
    'https://www.umairfabrics.com',
]));

$localOrigins = [
    'http://localhost:3000',
    'http://127.0.0.1:3000',
];

return [
    'paths' => ['api/*'],
    'allowed_methods' => ['*'],
    'allowed_origins' => array_values(array_unique(array_merge($productionOrigins, $localOrigins))),
  // Any localhost port (npm run dev may use 3001, etc.)
    'allowed_origins_patterns' => [
        '#^http://localhost(:\d+)?$#',
        '#^http://127\.0\.0\.1(:\d+)?$#',
    ],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => false,
];
