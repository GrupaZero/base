<?php

namespace App;

use Dotenv\Dotenv;
use Gzero\Base\ServiceProvider as ServiceProvider;
use Laravel\Passport\Passport;
use Orchestra\Testbench\Traits\CreatesApplication;

require __DIR__ . '/vendor/autoload.php';

if (file_exists(__DIR__ . '/.env.testing')) {
    (new Dotenv(__DIR__, '.env.testing'))->load();
}

$Laravel = new class {
    use CreatesApplication;

    protected function getPackageProviders($app)
    {
        $routes = $app['router']->getRoutes();

        // The URL generator needs the route collection that exists on the router.
        // Keep in mind this is an object, so we're passing by references here
        // and all the registered routes will be available to the generator.
        $app->instance('routes', $routes);

        //// Register Exception handler
        //$app->singleton(
        //    \Illuminate\Contracts\Debug\ExceptionHandler::class,
        //    \Gzero\Core\Exceptions\Handler::class
        //);

        // We need to tell Laravel Passport where to find oauth keys
        Passport::loadKeysFrom(__DIR__ . '/vendor/gzero/testing/oauth/');

        return [
            ServiceProvider::class
        ];
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application $app
     *
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        /** @TODO Why I need to do this here? Are we fine with overriding config options in service providers? */
        // Use passport as guard for api
        $app['config']->set('auth.guards.api.driver', 'passport');
        // We want to return Access-Control-Allow-Credentials header as well
        $app['config']->set('cors.supportsCredentials', true);
    }
};


$app = $Laravel->createApplication();

return $app;