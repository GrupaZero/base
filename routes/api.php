<?php

// Admin API
use Barryvdh\Cors\HandleCors;
use Gzero\Base\Http\Middleware\AdminApiAccess;

Route::group(
    [
        'domain'     => 'api.' . config('gzero.domain'),
        'prefix'     => 'v1/admin',
        'namespace'  => 'Gzero\Base\Http\Controllers\Api',
        'middleware' => [HandleCors::class, 'auth:api', AdminApiAccess::class]
    ],
    function ($router) {
        /** @var \Illuminate\Routing\Router $router */

        // ======== Languages ========
        $router->resource(
            'languages',
            'AdminLanguageController',
            ['only' => ['index', 'show']]
        );

        // ======== Users ========
        $router->get('users/', 'AdminUserController@index');
        $router->get('users/{id}', 'AdminUserController@show');
        $router->patch('users/{id}', 'AdminUserController@update');
        $router->delete('users/{id}', 'AdminUserController@destroy');

        // ======== Options ========
        $router->resource(
            'options',
            'AdminOptionController',
            ['only' => ['index', 'show', 'update']]
        );
    }
);

// Public API
Route::group(
    [
        'domain'     => 'api.' . config('gzero.domain'),
        'prefix'     => 'v1/user',
        'namespace'  => 'Gzero\Base\Http\Controllers\Api',
        'middleware' => [HandleCors::class, 'auth:api']
    ],
    function ($router) {
        /** @var \Illuminate\Routing\Router $router */

        // ======== Account ========
        $router->patch('account', 'PublicAccountController@update');

    }
);
