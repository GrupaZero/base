<?php namespace Base;

use Illuminate\Routing\Router;

class AuthCest {

    public function _before(FunctionalTester $I)
    {
        $I->haveMlRoutes(function ($router, $language) {
            /** @var Router $router */
            $router->get('/', function () {
                return 'Home: ' . app()->getLocale();
            })->name(mlSuffix('home', $language));
        });
    }

    public function canAccessLoginPage(FunctionalTester $I)
    {
        $I->amOnPage(route('login'));
        $I->seeResponseCodeIs(200);

        $I->see('Login', 'h1');
        $I->seeInTitle('Login');
        $I->see('E-mail', 'label');
        $I->see('Password', 'label');
        $I->see('Remember me', 'label');

        $I->seeInField('email', null);
        $I->seeInField('password', null);
        $I->see('Login', 'button[type=submit]');

        $I->seeLink('Forgot password?', route('password.request'));
        $I->seeLink('Register', route('register'));
    }

    public function canAccessRegisterPage(FunctionalTester $I)
    {
        $I->amOnPage(route('register'));
        $I->seeResponseCodeIs(200);

        $I->see('Register', 'h1');
        $I->seeInTitle('Register');
        $I->see('E-mail', 'label');
        $I->see('Nick name', 'label');
        $I->see('First Name', 'label');
        $I->see('Last Name', 'label');
        $I->see('Password', 'label');

        $I->seeInField('email', null);
        $I->seeInField('name', null);
        $I->seeInField('first_name', null);
        $I->seeInField('last_name', null);
        $I->seeInField('password', null);
        $I->see('Register', 'button[type=submit]');
    }

    public function canAccessForgotPasswordPage(FunctionalTester $I)
    {
        $I->amOnPage(route('password.request'));
        $I->seeResponseCodeIs(200);

        $I->see('Reset Password', 'h1');
        $I->seeInTitle('Reset Password');
        $I->see('E-mail', 'label');

        $I->seeInField('email', null);
        $I->see('Send password reset link', 'button[type=submit]');
    }
}

