<?php

namespace Base;

use Gzero\Base\Service\LanguageService;
use Illuminate\Foundation\Application;

class AppCest {

    public function _before(FunctionalTester $I)
    {
    }

    public function _after(FunctionalTester $I)
    {
    }

    public function applicationWorks(FunctionalTester $I)
    {
        $I->haveApplicationHandler(function ($app) {
            /** @var Application $app */
            $app->make('router')
                ->get(
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

        $I->haveApplicationHandler(function ($app) {
            /** @var Application $app */
            addMultiLanguageRoutes(function ($router) {
                $router->get('multi-language-content', function () {
                    return 'Laravel Multi Language Content: ' . app()->getLocale();
                });
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

        $I->haveApplicationHandler(function ($app) {
            /** @var Application $app */
            addMultiLanguageRoutes(function ($router) {
                $router->get('test', function () {
                    return 'Laravel Multi Language Content: ' . app()->getLocale();
                });
            });
            $app->make('router')
                ->middleware('web')
                ->get('{path?}', function () {
                    return 'Dynamic Router: ' . app()->getLocale();
                })
                ->where('path', '.*');
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
}
