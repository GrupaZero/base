<?php

// Admin API
use Barryvdh\Cors\HandleCors;
use Gzero\Base\Http\Middleware\AdminAccess;

Route::group(
    [
        'domain'     => 'api.' . config('gzero.domain'),
        'prefix'     => 'v1',
        'namespace'  => 'Gzero\Base\Http\Controllers\Api',
        'middleware' => [HandleCors::class, 'auth:api', AdminAccess::class]
    ],
    function ($router) {
        /** @var \Illuminate\Routing\Router $router */

        // ======== Users ========
        $router->get('users', 'UserController@index');
        $router->get('users/{id}', 'UserController@show');
        $router->patch('users/{id}', 'UserController@update');
        $router->delete('users/{id}', 'UserController@destroy');

        // ======== Options ========
        $router->put('options/{category}', 'OptionController@update');
    }
);

// Public API
Route::group(
    [
        'domain'     => 'api.' . config('gzero.domain'),
        'prefix'     => 'v1',
        'namespace'  => 'Gzero\Base\Http\Controllers\Api',
        'middleware' => [HandleCors::class]
    ],
    function ($router) {
        /** @var \Illuminate\Routing\Router $router */
        $router->group(['middleware' => ['auth:api']], function ($router) {
            /** @var \Illuminate\Routing\Router $router */

            // ======== Users ========
            $router->patch('users/me', 'UserController@updateMe');
        });

        // ======== Languages ========
        $router->get('languages', 'LanguageController@index');
        $router->get('languages/{code}', 'LanguageController@show')->where('code', '[a-z]{2}');

        // ======== Options ========
        $router->get('options', 'OptionController@index');
        $router->get('options/{category}', 'OptionController@show');
    }
);
