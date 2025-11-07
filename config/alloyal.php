<?php
$version = env('ASAAS_API_VERSION');

return [
    'sandbox' => [
        'base_url' => env('BASE_URL_HML'),
        'business_id' => env('BUSINESS_ID'),
        'token' => env('X_ClientEmployee_Token'),
        'email' => env('X_ClientEmployee_Email'),
        'password' => env('password'),
    ],
    'production' => [
        'base_url' => env('BASE_URL_PROD'),
        'business_id' => env('BUSINESS_ID'),
        'token' => env('X_ClientEmployee_Token'),
        'email' => env('X_ClientEmployee_Email'),
        'password' => env('password'),
    ],
];
