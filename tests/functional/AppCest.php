<?php

namespace Base;

class AppCest {

    public function _before(FunctionalTester $I)
    {
    }

    public function _after(FunctionalTester $I)
    {
    }

    public function applicationWorks(FunctionalTester $I)
    {
        $I->getApplication()
            ->make('router')
            ->get(
                '/',
                function () {
                    return 'Laravel';
                }
            );

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

    //public function inRedirectsToDefaultLanguageIfMultilangueIsSet(FunctionalTester $I)
    //{
    //    $app = $I->getApplication();
    //    $app->make('router')
    //        ->get(
    //            '/en',
    //            function () {
    //                return 'Laravel';
    //            }
    //        );
    //    $app->make('config')->set('gzero.multilang.enabled', true);
    //
    //    $I->amOnPage('/');
    //
    //    $I->see('Laravel');
    //
    //
    //}
}
