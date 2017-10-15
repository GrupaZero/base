<?php namespace Base\Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

use Gzero\Base\Model\User;
use Illuminate\Routing\Router;

class Functional extends \Codeception\Module {

    /**
     * Create user and return entity
     *
     * @param array $attributes
     *
     * @return User
     */
    public function haveUser($attributes = [])
    {
        return factory(User::class)->create($attributes);
    }

    /**
     * @param callable $closure
     */
    public function haveMlRoutes(callable $closure)
    {
        $this->getModule('Laravel5')
            ->haveApplicationHandler(function ($app) use ($closure) {
                addMultiLanguageRoutes(function ($router, $language) use ($closure) {
                    /** @var Router $router */
                    $closure($router, $language);

                    $router->getRoutes()->refreshActionLookups();
                    $router->getRoutes()->refreshNameLookups();
                });
            });
    }

    /**
     * @param callable $closure
     */
    public function haveRoutes(callable $closure)
    {
        $this->getModule('Laravel5')
            ->haveApplicationHandler(function ($app) use ($closure) {
                /** @var Router $router */
                $router = $app->make('router');
                $closure($router);

                $router->getRoutes()->refreshActionLookups();
                $router->getRoutes()->refreshNameLookups();
            });
    }

    /**
     * It clears all application handlers
     */
    public function clearRoutes()
    {
        $this->getModule('Laravel5')->clearApplicationHandlers();
    }

}
