<?php namespace Base;

trait AdminApiTest {

    public function _before(FunctionalTester $I)
    {
        $I->stopFollowingRedirects();
        $I->loginAsAdmin();
    }
}