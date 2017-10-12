<?php namespace Gzero\Base;

use Gzero\Base\Middleware\Init;
use Gzero\Base\Middleware\MultiLanguage;
use Gzero\Base\Service\LanguageService;
use Gzero\Base\Service\OptionService;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Foundation\Application;
use Illuminate\Routing\Router;
use Laravel\Passport\PassportServiceProvider;
use Robbo\Presenter\PresenterServiceProvider;

class ServiceProvider extends AbstractServiceProvider {

    /**
     * List of additional providers
     *
     * @var array
     */
    protected $providers = [
        PresenterServiceProvider::class,
        PassportServiceProvider::class
    ];

    /**
     * List of service providers aliases
     *
     * @var array
     */
    protected $aliases = [
        'options' => OptionService::class
    ];

    ///**
    // * The policy mappings for the application.
    // * @TODO What with policies?
    // * @var array
    // */
    //protected $policies = [
    //    Block::class   => BlockPolicy::class,
    //    Content::class => ContentPolicy::class,
    //    File::class    => FilePolicy::class,
    //    User::class    => UserPolicy::class,
    //    Option::class  => OptionPolicy::class
    //];

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
        $this->registerPolicies();
        $this->registerMigrations();
        $this->registerFactories();
        $this->registerMiddleware();
        $this->registerPublishes();
    }

    ///**
    // * Try to detect language from uri
    // * We need to do that as soon as possible, because we need to know what language need to be set for ML routes
    // *
    // * @return void
    // */
    //protected function detectLanguage()
    //{
    //    if (request()->segment(1) != 'admin' && $this->app['config']['gzero.ml']) {
    //        if ($this->app['config']['gzero.ml.subdomain']) {
    //            $locale = preg_replace('/\..+$/', '', request()->getHost());
    //        } else {
    //            $locale = request()->segment(1);
    //        }
    //        $languages = ['pl', 'en'];
    //        if (in_array($locale, $languages, true)) {
    //            app()->setLocale($locale);
    //            $this->app['config']['gzero.ml.detected'] = true;
    //        }
    //    }
    //}

    /**
     * It registers default locale
     *
     * @throws Exception
     *
     * @return void
     */
    protected function setDefaultLocale()
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
        //        return app('filesystem')->disk(config('gzero.upload.disk'))->getDriver();
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
        //$gate = app('Illuminate\Contracts\Auth\Access\Gate');
        //$gate->before(
        //    function ($user) {
        //        if ($user->isSuperAdmin()) {
        //            return true;
        //        }
        //
        //        if ($user->isGuest()) {
        //            return false;
        //        }
        //    }
        //);
        //foreach ($this->policies as $key => $value) {
        //    $gate->policy($key, $value);
        //}
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
        require_once __DIR__ . '/helpers.php';
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
        app(Factory::class)->load(__DIR__ . '/../../../database/factories');
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
            ]
        );

        // Factories
        $this->publishes(
            [
                __DIR__ . '/../../../database/factories/UserFactory.php' => database_path('factories/gzero.php'),
            ]
        );

        // Views
        $this->publishes(
            [
                __DIR__ . '/../../../resources/views' => resource_path('views/vendor/gzero-base'),
            ]
        );
    }

}
