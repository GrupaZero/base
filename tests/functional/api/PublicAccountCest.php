<?php namespace Base;

class PublicAccountCest {

    public function updateAccount(FunctionalTester $I)
    {
        $user = $I->haveUser();
        $I->loginWithToken($user->email);

        $I->sendPATCH(apiUrl('user/account'),
            [
                'name'      => 'Modified user',
                'firstName' => 'Johny',
                'lastName'  => 'Stark',
                'email'     => 'newEmail@example.com'
            ]
        );

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(
            [
                'name'      => 'Modified user',
                'firstName' => 'Johny',
                'lastName'  => 'Stark',
                'email'     => 'newEmail@example.com',
            ]
        );
    }

    public function updatePassword(FunctionalTester $I)
    {
        $user = $I->haveUser();
        $I->loginWithToken($user->email);

        $I->sendPATCH(apiUrl('user/account'),
            [
                'name'                  => $user->name,
                'email'                 => $user->email,
                'password'              => 'newPassword',
                'password_confirmation' => 'newPassword',
            ]
        );
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->deleteHeader('Authorization');
        $I->login($user->email, 'newPassword');
    }

    public function cantChangePasswordWithoutConfirmation(FunctionalTester $I)
    {
        $user = $I->haveUser();
        $I->loginWithToken($user->email);
        $I->sendPATCH(apiUrl('user/account'),
            [
                'name'     => $user->name,
                'email'    => $user->email,
                'password' => 'newPassword',
            ]
        );
        $I->seeResponseCodeIs(422);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(
            [
                'message' => 'The given data was invalid.',
                'errors'  => [
                    'password' => ['The password and password confirmation must match.']
                ]
            ]
        );
    }

    public function cantChangeNameToAlreadyTaken(FunctionalTester $I)
    {
        $user  = $I->haveUser();
        $user2 = $I->haveUser();
        $I->loginWithToken($user->email);

        $I->sendPATCH(apiUrl('user/account'),
            [
                'name'  => $user2->name,
                'email' => $user->email,
            ]
        );

        $I->seeResponseCodeIs(422);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(
            [
                'message' => 'The given data was invalid.',
                'errors'  => [
                    'name' => ['The name has already been taken.']
                ]
            ]
        );
    }

    public function cantChangeEmailToAlreadyTaken(FunctionalTester $I)
    {
        $user  = $I->haveUser();
        $user2 = $I->haveUser();
        $I->loginWithToken($user->email);

        $I->sendPATCH(apiUrl('user/account'),
            [
                'name'  => $user->name,
                'email' => $user2->email,
            ]
        );

        $I->seeResponseCodeIs(422);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(
            [
                'message' => 'The given data was invalid.',
                'errors'  => [
                    'email' => ['The email has already been taken.']
                ]
            ]
        );
    }

}
