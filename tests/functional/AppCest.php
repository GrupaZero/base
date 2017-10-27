<?php namespace Base;

use Gzero\Base\Events\RouteMatched;
use Gzero\Base\Model\Language;
use Gzero\Base\Model\Routable;
use Gzero\Base\Model\Route;
use Gzero\Base\Model\RouteTranslation;
use Gzero\Base\Queries\RouteQuery;
use Gzero\Base\Service\LanguageService;
use Illuminate\Http\Response;
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
        // @TODO Try to move this to factories
        $route = new Route();
        $route->setRelation('translations', collect([new RouteTranslation(['language_code' => 'en', 'is_active' => true])]));
        $route->setRelation('routable', new class implements Routable {
            public function handle(Route $route, Language $lang): Response
            {
                return response('Hello World');
            }
        });

        $I->haveInstance(RouteQuery::class, Mockery::mock(RouteQuery::class, [
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
            $router->get('{path?}', 'Gzero\Base\Http\Controller\RouteController@dynamicRouter')->where('path', '.*');
        });


        $I->amOnPage('multi-language-content');
        $I->seeResponseCodeIs(200);
        $I->see('Hello World');
        $I->canSeeEventTriggered(RouteMatched::class);

    }

}
