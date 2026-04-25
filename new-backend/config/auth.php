<?php

use App\Models\Admin;
use App\Models\Customer;
use App\Models\DeliveryPerson;

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Defaults
    |--------------------------------------------------------------------------
    */

    'defaults' => [
        'guard' => env('AUTH_GUARD', 'sanctum'),
        'passwords' => env('AUTH_PASSWORD_BROKER', 'customers'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Guards
    |--------------------------------------------------------------------------
    |
    | The `sanctum` guard is registered automatically by the Sanctum package
    | and resolves the authenticated user based on the presented bearer
    | token's tokenable (either an Admin or a Customer). Route-level
    | authorization is performed via Sanctum's `abilities:...` middleware.
    |
    */

    'guards' => [
        'admin' => [
            'driver' => 'sanctum',
            'provider' => 'admins',
        ],

        'customer' => [
            'driver' => 'sanctum',
            'provider' => 'customers',
        ],

        'delivery' => [
            'driver' => 'sanctum',
            'provider' => 'delivery_persons',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Providers
    |--------------------------------------------------------------------------
    */

    'providers' => [
        'admins' => [
            'driver' => 'eloquent',
            'model' => Admin::class,
        ],

        'customers' => [
            'driver' => 'eloquent',
            'model' => Customer::class,
        ],

        'delivery_persons' => [
            'driver' => 'eloquent',
            'model' => DeliveryPerson::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Resetting Passwords
    |--------------------------------------------------------------------------
    */

    'passwords' => [
        'customers' => [
            'provider' => 'customers',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],

        'admins' => [
            'provider' => 'admins',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),

];
