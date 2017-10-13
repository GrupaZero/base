<?php

Route::group(
    [
        'namespace'  => 'Gzero\Base\Http\Controller',
        'middleware' => ['web', 'auth']
    ],
    function ($router) {
        /** @var \Illuminate\Routing\Router $router */

        // ======== Account ========
        $router->get('account', 'AccountController@index')->name('account');
        $router->get('account/edit', 'AccountController@edit')->name('account.edit');
        $router->get('account/welcome', 'AccountController@welcome')->name('account.welcome');
        $router->get('account/oauth', 'AccountController@oauth')->name('account.oauth');
    }
);
