<?php

namespace Base;

use Gzero\Base\Middleware\MultiLang;
use Gzero\Base\Service\LanguageService;
use Illuminate\Contracts\Http\Kernel;
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
        $I->haveApplicationHandler(function($app) {
            /** @var Application $app */
            $app->make('router')
                ->get(
                    '/',
                    function() {
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
        $I->seeResponseContains('Redirecting to http://localhost/test-content');
    }

    public function inRedirectsToDefaultLanguageIfMultiLanguageIsSet(FunctionalTester $I)
    {
        $I->stopFollowingRedirects();
        $I->disableExceptionHandling();

        $I->haveApplicationHandler(function($app) {
            /** @var Application $app */
            $app->make('config')->set('gzero.ml', true);
            $app->make(Kernel::class)->prependMiddleware(MultiLang::class);
            $app->make('router')
                ->get(
                    '/en/test-content',
                    function() {
                        return 'Laravel';
                    }
                );
        });
        $I->haveInstance(LanguageService::class, new class {
            function getAllEnabled()
            {
                return collect([
                    (object) ['code' => 'en'],
                    (object) ['code' => 'pl']
                ]);
            }

            function getDefault()
            {
                return (object) ['code' => 'en'];
            }
        });


        $I->amOnPage('/test-content');
        $I->seeResponseCodeIs(301);
        $I->seeResponseContains('Redirecting to http://localhost/en/test-content');

        $I->clearApplicationHandlers();
        $I->haveApplicationHandler(function($app) {
            /** @var Application $app */
            $app->make('config')->set('gzero.ml', true);
            $app->make(Kernel::class)->prependMiddleware(MultiLang::class);
            $app->make('router')
                ->get(
                    '/pl/test-content',
                    function() {
                        return 'Laravel';
                    }
                );
        });
        $I->haveInstance(LanguageService::class, new class {
            function getAllEnabled()
            {
                return collect([
                    (object) ['code' => 'en'],
                    (object) ['code' => 'pl']
                ]);
            }

            function getDefault()
            {
                return (object) ['code' => 'pl'];
            }
        });

        $I->amOnPage('/test-content');
        $I->seeResponseCodeIs(301);
        $I->seeResponseContains('Redirecting to http://localhost/pl/test-content');
    }
}
