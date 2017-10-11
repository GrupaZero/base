<?php

namespace Base;

use Base\FunctionalTester;

class ServiceProvioderCest {
    public function _before(FunctionalTester $I)
    {
    }

    public function _after(FunctionalTester $I)
    {
    }

    // tests
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
}
