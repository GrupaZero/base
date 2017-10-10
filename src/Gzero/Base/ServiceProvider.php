<?php namespace Gzero\Base;

use Gzero\Repository\LangService;
use Illuminate\Foundation\Application;
use Robbo\Presenter\PresenterServiceProvider;

/**
 * This file is part of the GZERO CMS package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Class ServiceProvider
 *
 * @package    Gzero
 * @author     Adrian Skierniewski <adrian.skierniewski@gmail.com>
 * @copyright  Copyright (c) 2014, Adrian Skierniewski
 */
class ServiceProvider extends AbstractServiceProvider {

    /**
     * List of additional providers
     *
     * @var array
     */
    protected $providers = [
        PresenterServiceProvider::class,
    ];

    /**
     * List of service providers aliases
     *
     * @var array
     */
    protected $aliases = [
        'options' => OptionsService::class
    ];

    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        Block::class   => BlockPolicy::class,
        Content::class => ContentPolicy::class,
        File::class    => FilePolicy::class,
        User::class    => UserPolicy::class,
        Option::class  => OptionPolicy::class
    ];

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        parent::register();
        $this->registerConfig();
        $this->registerHelpers();
        $this->bindRepositories();
        $this->bindTypes();
        $this->bindOtherStuff();
    }

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->detectLanguage();
        $this->registerCommands();
        $this->registerPolicies();
        $this->registerMigrations();
    }

    /**
     * Try to detect language from uri
     * We need to do that as soon as possible, because we need to know what language need to be set for ML routes
     *
     * @return void
     */
    protected function detectLanguage()
    {
        if (request()->segment(1) != 'admin' && $this->app['config']['gzero.multilang.enabled']) {
            if ($this->app['config']['gzero.multilang.subdomain']) {
                $locale = preg_replace('/\..+$/', '', request()->getHost());
            } else {
                $locale = request()->segment(1);
            }
            $languages = ['pl', 'en'];
            if (in_array($locale, $languages, true)) {
                app()->setLocale($locale);
                $this->app['config']['gzero.multilang.detected'] = true;
            }
        }
    }

    /**
     * Bind Doctrine 2 repositories
     *
     * @return void
     */
    protected function bindRepositories()
    {
        $this->app->singleton(
            'gzero.menu.account',
            function () {
                return new Register();
            }
        );

        // We need only one LangRepository
        $this->app->singleton(
            'Gzero\Repository\LangService',
            function (Application $app) {
                return new LangService($app->make('cache'));
            }
        );

        $this->app->singleton(
            'croppa.src_dir',
            function () {
                return app('filesystem')->disk(config('gzero.upload.disk'))->getDriver();
            }
        );
    }

    /**
     * Bind entities types classes
     *
     * @return void
     */
    protected function bindTypes()
    {
        $entities = [
            'block',
            'content',
            'file'
        ];

        foreach ($entities as $entity) {
            $key = "gzero.$entity" . '_type';
            if (isset($this->app['config'][$key])) {
                foreach ($this->app['config'][$key] as $type => $class) {
                    $this->app->bind("$entity:type:$type", $class);
                }
            }
        }
    }

    /**
     * Register additional commands
     *
     * @return void
     */
    public function registerCommands()
    {
        //
    }

    /**
     * Register polices
     *
     * @return void
     */
    protected function registerPolicies()
    {
        $gate = app('Illuminate\Contracts\Auth\Access\Gate');
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
        app()->singleton(
            'Gzero\Core\OptionsService',
            function (Application $app) {
                return new OptionsService($app->make('Gzero\Repository\OptionService'));
            }
        );
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
    protected function registerConfig()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/config/gzero.php',
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
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
    }

}
