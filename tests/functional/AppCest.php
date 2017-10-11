<?php

namespace Base;

use Base\FunctionalTester;

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
}
