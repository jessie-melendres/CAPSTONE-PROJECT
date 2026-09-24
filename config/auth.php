<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Authentication Defaults
    |--------------------------------------------------------------------------
    |
    | This application does NOT use Laravel's built-in Auth facade/guards for
    | its own login flow — PortalController authenticates against the users
    | table directly and stores role/user_id in the plain session (see
    | app/Http/Controllers/PortalController.php). This file exists only so
    | framework internals that expect an auth config to be present (e.g. the
    | `throttle` middleware's request-signature resolution) don't error out.
    |
    */
    'defaults' => [
        'guard' => 'web',
        'passwords' => 'users',
    ],

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],
    ],

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => App\Models\User::class,
        ],
    ],

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => 10800,
];
