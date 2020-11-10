<?php

use Modules\ApplicationAuth\Entities\User;
use Modules\ApplicationAuth\Transformers\UserResource;
use Modules\ApplicationAuth\Http\Controllers\AuthController;
use Modules\ApplicationAuth\Http\Controllers\UserController;

return [
    'auth' => [
        /*
         * The name of the auth guard.
         */
        'guard' => 'application',

        /*
         * The name for the user provider.
         */
        'provider' => 'users',

        /*
         * The token refresh ttl for guest users, set this to a long time (default is 10 years) so the token can be
         * refreshed again even if the user has not used the application for a long time.
         */
        'guest_refresh_ttl' => env('JWT_GUEST_REFRESH_TTL', 5256000),

    ],

    /*
     * The controllers to use.
     */
    'controllers' => [
        'auth' => AuthController::class,
        'user' => UserController::class,
    ],

    /*
     * The model to use, it must extend \App\Models\User or it will throw errors
     * in various parts of the module.
     */
    'models' => [
        'user' => User::class,
    ],

    /*
     * The resource to use for responses, it must extend \Modules\ApplicationAuth\Transformers\ApplicationUser or it
     * will throw errors in various parts of the module.
     */
    'resource' => UserResource::class,

    /*
     * The locales that can be set on the user.
     */
    'locales' => ['en'],
];
