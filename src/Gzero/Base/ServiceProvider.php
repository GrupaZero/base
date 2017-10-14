<?php namespace Gzero\Base;

use Carbon\Carbon;
use Gzero\Base\Http\Middleware\Init;
use Gzero\Base\Http\Middleware\MultiLanguage;
use Gzero\Base\Http\Middleware\ViewShareUser;
use Gzero\Base\Model\Option;
use Gzero\Base\Model\User;
use Gzero\Base\Service\LanguageService;
use Gzero\Base\Service\OptionService;
use Gzero\Base\Policies\OptionPolicy;
use Gzero\Base\Policies\UserPolicy;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Foundation\Application;
use Illuminate\Routing\Router;
use Laravel\Passport\Passport;
use Robbo\Presenter\PresenterServiceProvider;

class ServiceProvider extends AbstractServiceProvider {

    /**
     * List of additional providers
     *
     * @var array
     */
    protected $providers = [
        PresenterServiceProvider::class
    ];

    /**
     * List of service providers aliases
     *
     * @var array
     */
    protected $aliases = [
        'options' => OptionService::class
    ];

    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        User::class   => UserPolicy::class,
        Option::class => OptionPolicy::class
    ];

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        parent::register();
        $this->mergeConfig();
        $this->registerHelpers();
        $this->bindRepositories();
        $this->bindOtherStuff();
    }

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->setDefaultLocale();

        $this->registerRoutes();

        /** @TODO Probably we can move this to routes file */
        Passport::routes();

        Passport::tokensExpireIn(Carbon::now()->addDays(15));

        Passport::refreshTokensExpireIn(Carbon::now()->addDays(30));

        $this->registerPolicies();
        $this->registerMigrations();
        $this->registerFactories();
        $this->registerMiddleware();
        $this->registerViews();
        $this->registerPublishes();
    }

    /**
     * It registers default locale
     *
     * @throws Exception
     *
     * @return void
     */
    public static function setDefaultLocale()
    {
        $defaultLanguage = resolve(LanguageService::class)->getDefault();
        if (empty($defaultLanguage)) {
            throw new Exception('No default language found');
        }
        app()->setLocale($defaultLanguage->code);
    }

    /**
     * Bind services
     *
     * @return void
     */
    protected function bindRepositories()
    {
        $this->app->singleton(
            LanguageService::class,
            function (Application $app) {
                return new LanguageService($app->make('cache'));
            }
        );

        //$this->app->singleton(
        //    'gzero.menu.account',
        //    function () {
        //        return new Register();
        //    }
        //);
        //
        //$this->app->singleton(
        //    'croppa.src_dir',
        //    function () {
        //        return resolve('filesystem')->disk(config('gzero.upload.disk'))->getDriver();
        //    }
        //);
    }

    /**
     * Register polices
     *
     * @return void
     */
    protected function registerPolicies()
    {
        $gate = resolve(Gate::class);
        $gate->before(
            function ($user) {
                if ($user->isSuperAdmin()) {
                    return true;
                }

                if ($user->isGuest()) {
                    return false;
                }
            }
        );
        foreach ($this->policies as $key => $value) {
            $gate->policy($key, $value);
        }
    }

    /**
     * Bind other services
     *
     * @return void
     */
    protected function bindOtherStuff()
    {
        //
    }

    /**
     * Add additional file to store helpers
     *
     * @return void
     */
    protected function registerHelpers()
    {
        require __DIR__ . '/helpers.php';
    }

    /**
     * It registers gzero config
     *
     * @return void
     */
    protected function mergeConfig()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../../config/config.php',
            'gzero'
        );
    }

    /**
     * It registers db migrations
     *
     * @return void
     */
    protected function registerMigrations()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../../../database/migrations');
    }

    /**
     * It registers factories
     *
     * @return void
     */
    protected function registerFactories()
    {
        resolve(Factory::class)->load(__DIR__ . '/../../../database/factories');
    }

    /**
     * It register all middleware
     *
     * @return void
     */
    protected function registerMiddleware()
    {
        resolve(Kernel::class)->prependMiddleware(Init::class);
        resolve(Router::class)->prependMiddlewareToGroup('web', MultiLanguage::class);
        resolve(Router::class)->pushMiddlewareToGroup('web', ViewShareUser::class);
    }

    /**
     * It register all views
     *
     * @return void
     */
    protected function registerViews()
    {
        $this->loadViewsFrom(__DIR__ . '/../../../resources/views', 'gzero-base');
    }

    /**
     * Add additional file to store routes
     *
     * @return void
     */
    protected function registerRoutes()
    {
        $this->loadRoutesFrom(__DIR__ . '/../../../routes/web.php');
        $this->loadRoutesFrom(__DIR__ . '/../../../routes/api.php');
    }

    /**
     * It registers all assets to publish
     *
     * @return void
     */
    protected function registerPublishes()
    {
        // Config
        $this->publishes(
            [
                __DIR__ . '/../../../config/config.php' => config_path('gzero.php'),
            ],
            'config'
        );

        // Factories
        $this->publishes(
            [
                __DIR__ . '/../../../database/factories/UserFactory.php' => database_path('factories/gzero.php'),
            ],
            'factories'
        );

        // Views
        $this->publishes(
            [
                __DIR__ . '/../../../resources/views' => resource_path('views/vendor/gzero-base'),
            ],
            'views'
        );
    }

}
