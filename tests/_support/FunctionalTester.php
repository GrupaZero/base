<?php namespace Base;

use Base\_generated\FunctionalTesterActions;
use Gzero\Base\Models\User;

/**
 * Inherited Methods
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method \Codeception\Lib\Friend haveFriend($name, $actorClass = null)
 *
 */
class FunctionalTester extends \Codeception\Actor {

    use FunctionalTesterActions;

    /**
     * Login with token and set Authorization header
     *
     * @param $email
     *
     * @return User
     */
    public function loginWithToken($email)
    {
        $I    = $this;
        $user = User::where('email', $email)->first();
        $I->assertInstanceOf(User::class, $user);
        $I->amBearerAuthenticated($user->createToken('Test')->accessToken);
        return $user;
    }

    /**
     * Login as admin in to app
     *
     * @return User
     */
    public function loginAsAdmin()
    {
        return $this->loginWithToken('admin@gzero.pl');
    }

    /**
     * Login as normal user
     *
     * @return User
     */
    public function loginAsUser()
    {
        return $this->loginWithToken($this->haveUser()->email);
    }

    /**
     * Login in to app
     *
     * @param $email
     * @param $password
     */
    public function login($email, $password)
    {
        $I = $this;
        $I->amLoggedAs(['email' => $email, 'password' => $password], 'web');
        $I->seeAuthentication();
    }
}
