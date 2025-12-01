<?php

return [
    'production' => [
        'url' => env('YOUCAST_PRODUCTION_URL'),
        'login' => env('YOUCAST_LOGIN'),
        'secret' => env('YOUCAST_SECRET'),
    ],
    'dahplay_mw' => [
        'url' => env('DAHPLAY_MW_URL'),
        'email' => env('DAHPLAY_EMAIL'),
        'secret' => env('DAHPLAY_SECRET'),
    ],
];
