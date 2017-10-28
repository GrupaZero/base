<?php namespace Base;

use Gzero\Base\Events\RouteMatched;
use Gzero\Base\Models\Language;
use Gzero\Base\Models\Permission;
use Gzero\Base\Models\Role;
use Gzero\Base\Models\Route;
use Gzero\Base\Models\User;
use Gzero\Base\Services\LanguageService;
use Gzero\Base\Services\RouteQueryService;
use Illuminate\Routing\Router;
use Mockery;

class AppCest {

    public function applicationWorks(FunctionalTester $I)
    {
        $I->haveRoutes(function ($router) {
            /** @var Router $router */
            $router->get(
                '/',
                function () {
                    return 'Laravel';
                }
            );
        });

        $I->amOnPage('/');

        $I->see('Laravel');
    }

    public function itRedirectsRequestsWithIndexPHP(FunctionalTester $I)
    {
        $I->stopFollowingRedirects();

        $I->amOnPage('/index.php/test-content');

        $I->seeResponseCodeIs(301);
        $I->seeResponseContains('Redirecting to http://dev.gzero.pl/test-content');
    }

    public function itGeneratesMultiLanguageRoutesCorrectly(FunctionalTester $I)
    {
        $I->stopFollowingRedirects();

        $I->haveInstance(LanguageService::class, new class {
            function getAllEnabled()
            {
                return collect([
                    (object) ['code' => 'en', 'is_default' => true],
                    (object) ['code' => 'pl', 'is_default' => false]
                ]);
            }

            function getDefault()
            {
                return (object) ['code' => 'en', 'is_default' => true];
            }
        });

        $I->haveMlRoutes(function ($router, $languages) {
            /** @var Router $router */
            $router->get('multi-language-content', function () {
                return 'Laravel Multi Language Content: ' . app()->getLocale();
            });
        });

        $I->amOnPage('/multi-language-content');
        $I->seeResponseCodeIs(200);
        $I->see('Laravel Multi Language Content: en');

        $I->amOnPage('/pl/multi-language-content');
        $I->seeResponseCodeIs(200);
        $I->see('Laravel Multi Language Content: pl');

        $I->amOnPage('/en/multi-language-content');
        $I->seeResponseCodeIs(404);


        $I->clearApplicationHandlers();

        $I->haveInstance(LanguageService::class, new class {
            function getAllEnabled()
            {
                return collect([
                    (object) ['code' => 'en', 'is_default' => false],
                    (object) ['code' => 'pl', 'is_default' => true]
                ]);
            }

            function getDefault()
            {
                return (object) ['code' => 'pl', 'is_default' => true];
            }
        });

        $I->haveRoutes(function ($router) {
            /** @var Router $router */
            addMultiLanguageRoutes(function ($router, $languages) {
                $router->get('test', function () {
                    return 'Laravel Multi Language Content: ' . app()->getLocale();
                });
            });
            $router->middleware('web')
                ->get('{path?}', function () {
                    return 'Dynamic Router: ' . app()->getLocale();
                })->where('path', '.*');
        });


        $I->amOnPage('/test');
        $I->seeResponseCodeIs(200);
        $I->see('Laravel Multi Language Content: pl');

        $I->amOnPage('/en/test');
        $I->seeResponseCodeIs(200);
        $I->see('Laravel Multi Language Content: en');

        $I->amOnPage('/pl/test');
        $I->seeResponseCodeIs(200);
        $I->see('Dynamic Router: pl');
    }

    public function itWontSetLocaleWithoutMiddlewareGroup(FunctionalTester $I)
    {
        $I->haveInstance(LanguageService::class, new class {
            function getAllEnabled()
            {
                return collect([
                    (object) ['code' => 'en', 'is_default' => false],
                    (object) ['code' => 'pl', 'is_default' => true]
                ]);
            }

            function getDefault()
            {
                return (object) ['code' => 'pl', 'is_default' => true];
            }
        });

        $I->haveMlRoutes(function ($router, $langauge) {
            /** @var Router $router */
            $router->get('ml_route', function () {
                return 'Laravel Multi Language Content: ' . app()->getLocale();
            });
        });

        $I->haveRoutes(function ($router) {
            /** @var Router $router */
            $router->get('{path?}', function () {
                return 'Dynamic Router: ' . app()->getLocale();
            })->where('path', '.*');
        });


        $I->amOnPage('/ml_route');
        $I->seeResponseCodeIs(200);
        $I->see('Laravel Multi Language Content: pl');

        $I->amOnPage('/en/ml_route');
        $I->seeResponseCodeIs(200);
        $I->see('Laravel Multi Language Content: en');

        $I->amOnPage('/pl/test');
        $I->seeResponseCodeIs(200);
        // Should use en because our ServiceProvider set it once and we don't use middleware to override it
        $I->see('Dynamic Router: en');
    }

    public function canUseMultipleApplicationHandlersInSingleTest(FunctionalTester $I)
    {
        $I->haveInstance(LanguageService::class, new class {
            function getAllEnabled()
            {
                return collect([
                    (object) ['code' => 'en', 'is_default' => false],
                    (object) ['code' => 'pl', 'is_default' => true]
                ]);
            }

            function getDefault()
            {
                return (object) ['code' => 'pl', 'is_default' => true];
            }
        });

        $I->haveMlRoutes(function ($router, $language) {
            /** @var Router $router */
            $router->get('/', function () {
                return 'Home: ' . app()->getLocale();
            })->name(mlSuffix('home', $language));
        });

        $I->haveMlRoutes(function ($router, $language) {
            /** @var Router $router */
            $router->get('/test', function () {
                return 'Laravel: ' . app()->getLocale();
            })->name(mlSuffix('test', $language));
        });

        $I->haveRoutes(function ($router) {
            /** @var Router $router */
            $router->get('/contact', function () {
                return 'Contact';
            })->name('contact');
        });

        $I->amOnPage('/');

        $I->seeResponseCodeIs(200);
        $I->see('Home: pl');

        $I->amOnPage('/en/test');

        $I->seeResponseCodeIs(200);
        $I->see('Laravel: en');

        $I->amOnRoute('contact');

        $I->seeResponseCodeIs(200);
        $I->see('Contact');
    }

    public function dynamicRouterWorks(FunctionalTester $I)
    {
        $route = factory(Route::class)
            ->states('translationEn', 'routableHelloWorld')
            ->make();

        $I->haveInstance(RouteQueryService::class, Mockery::mock(RouteQueryService::class, [
            'getByPath' => $route,
        ]));

        $I->haveInstance(LanguageService::class, new LanguageService(
            collect([
                new Language(['code' => 'en', 'is_enabled' => true, 'is_default' => true]),
                new Language(['code' => 'pl', 'is_enabled' => true, 'is_default' => false]),
            ])
        ));

        $I->haveMlRoutes(function ($router, $languages) {
            /** @var Router $router */
            $router->get('{path?}', 'Gzero\Base\Http\Controllers\RouteController@dynamicRouter')->where('path', '.*');
        });


        $I->amOnPage('multi-language-content');
        $I->seeResponseCodeIs(200);
        $I->see('Hello World');
        $I->canSeeEventTriggered(RouteMatched::class);
    }

    //public function dynamicRouterAllowsAdminToViewInactiveRoute(FunctionalTester $I)
    //{
    //    $user = factory(User::class)->create();
    //    $role = factory(Role::class)->create(['name' => 'editor']);
    //    $viewInactive = factory(Permission::class)->states('viewInactive')->create();
    //    $route = factory(Route::class)
    //        ->states('inactiveTranslationEn', 'routableHelloWorld')
    //        ->make();
    //
    //    $user->roles()->attach($role);
    //    $role->permissions()->attach($viewInactive->id);
    //
    //    $I->login($user->email, 'secret');
    //
    //    $I->haveInstance(RouteQueryService::class, Mockery::mock(RouteQueryService::class, [
    //        'getByPath' => $route,
    //    ]));
    //
    //    $I->haveInstance(LanguageService::class, new LanguageService(
    //        collect([
    //            new Language(['code' => 'en', 'is_enabled' => true, 'is_default' => true]),
    //            new Language(['code' => 'pl', 'is_enabled' => true, 'is_default' => false]),
    //        ])
    //    ));
    //
    //    $I->haveMlRoutes(function ($router, $languages) {
    //        /** @var Router $router */
    //        $router->get('{path?}', 'Gzero\Base\Http\Controllers\RouteController@dynamicRouter')->where('path', '.*');
    //    });
    //
    //
    //    $I->amOnPage('multi-language-content');
    //    $I->seeResponseCodeIs(200);
    //    $I->see('Hello World');
    //    $I->canSeeEventTriggered(RouteMatched::class);
    //}
}
