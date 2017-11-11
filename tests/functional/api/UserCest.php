<?php namespace Base;

use Illuminate\Support\Facades\Hash;

class UserCest {

    public function adminShouldBeAbleToGetListOfUsers(FunctionalTester $I)
    {
        $I->loginAsAdmin();
        $usersNumber = 4;
        for ($i = 0; $i < $usersNumber; $i++) {
            $I->haveUser();
        }

        $I->sendGET(apiUrl('users'));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('data[*]');
        $I->seeResponseContainsJson(
            [
                'data'  => [
                    'id'         => 1,
                    'email'      => 'admin@gzero.pl',
                    'name'       => 'Admin',
                    'first_name' => 'John',
                    'last_name'  => 'Doe',
                    'roles'      => [
                        [
                            'id'   => 1,
                            'name' => 'Admin'
                        ]
                    ],
                ],
                'meta'  => [
                    'current_page' => 1,
                    'from'         => 1,
                    'last_page'    => 1,
                    'path'         => apiUrl('users'),
                    'per_page'     => 20,
                    'to'           => $usersNumber + 1,
                    'total'        => $usersNumber + 1,
                ],
                'links' => [
                    'first' => apiUrl('users') . '?page=1',
                    'last'  => apiUrl('users') . '?page=1',
                    'prev'  => null,
                    'next'  => null
                ],
            ]
        );
    }

    public function adminShouldBeAbleToGetSingleUser(FunctionalTester $I)
    {
        $I->loginAsAdmin();
        $user = $I->haveUser(
            [
                'name'       => 'Test user',
                'first_name' => 'John',
                'last_name'  => 'Doe',
                'password'   => Hash::make('test123')
            ]
        );

        $I->sendGet(apiUrl('users', [$user->id]));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->dontSeeResponseJsonMatchesJsonPath('data[*]');
        $I->seeResponseContainsJson(
            [
                'name'       => 'Test user',
                'first_name' => 'John',
                'last_name'  => 'Doe',
            ]
        );
    }

    public function adminShouldNotBeAbleToGetNonExistingUser(FunctionalTester $I)
    {
        $I->loginAsAdmin();

        $I->sendGET(apiUrl('users', [4]));

        $I->seeResponseCodeIs(404);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['message' => 'Not found']);
    }

    public function adminShouldBeAbleToUpdateUser(FunctionalTester $I)
    {
        $I->loginAsAdmin();
        $user = $I->haveUser();

        $I->sendPATCH(apiUrl('users', [$user->id]),
            [
                'name'      => 'Modified user',
                'firstName' => 'Johny',
                'lastName'  => 'Stark',
                'email'     => $user->email,
            ]
        );

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->dontSeeResponseJsonMatchesJsonPath('data[*]');
        $I->seeResponseContainsJson(
            [
                'name'       => 'Modified user',
                'first_name' => 'Johny',
                'last_name'  => 'Stark',
                'email'      => $user->email,
            ]
        );
    }

    public function adminShouldNotBeAbleToUpdateNonExistingUser(FunctionalTester $I)
    {
        $I->loginAsAdmin();

        $I->sendPATCH(apiUrl('users', [4]));

        $I->seeResponseCodeIs(404);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['message' => 'Not found']);
    }

    public function adminShouldBeAbleToDeleteUser(FunctionalTester $I)
    {
        $I->loginAsAdmin();
        $user = $I->haveUser();

        $I->sendDELETE(apiUrl('users', [$user->id]));

        $I->seeResponseCodeIs(204);
    }

    public function shouldBeAbleToUpdateMyPersonalInformation(FunctionalTester $I)
    {
        $I->loginAsUser();

        $I->sendPATCH(apiUrl('users/me'),
            [
                'name'       => 'Modified user',
                'first_name' => 'Johny',
                'last_name'  => 'Stark',
                'email'      => 'newEmail@example.com'
            ]
        );

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->dontSeeResponseJsonMatchesJsonPath('data[*]');
        $I->seeResponseContainsJson(
            [
                'name'       => 'Modified user',
                'first_name' => 'Johny',
                'last_name'  => 'Stark',
                'email'      => 'newEmail@example.com',
            ]
        );
    }

    public function shouldBeAbleToUpdateMyPassword(FunctionalTester $I)
    {
        $user = $I->loginAsUser();

        $I->sendPATCH(apiUrl('users/me'),
            [
                'name'                  => $user->name,
                'email'                 => $user->email,
                'password'              => 'newPassword',
                'password_confirmation' => 'newPassword',
            ]
        );

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->dontSeeResponseJsonMatchesJsonPath('data[*]');

        $I->deleteHeader('Authorization');
        $I->login($user->email, 'newPassword');
    }

    public function cantChangeMyPasswordWithoutConfirmation(FunctionalTester $I)
    {
        $user = $I->loginAsUser();

        $I->sendPATCH(apiUrl('users/me'),
            [
                'name'     => $user->name,
                'email'    => $user->email,
                'password' => 'newPassword',
            ]
        );

        $I->seeResponseCodeIs(422);
        $I->seeResponseIsJson();
        $I->dontSeeResponseJsonMatchesJsonPath('data[*]');
        $I->seeResponseContainsJson(
            [
                'message' => 'The given data was invalid.',
                'errors'  => [
                    'password' => ['The password and password confirmation must match.']
                ]
            ]
        );
    }

    public function cantChangeMyNameToAlreadyTaken(FunctionalTester $I)
    {
        $user  = $I->loginAsUser();
        $user2 = $I->haveUser();

        $I->sendPATCH(apiUrl('users/me'),
            [
                'name'  => $user2->name,
                'email' => $user->email,
            ]
        );

        $I->seeResponseCodeIs(422);
        $I->seeResponseIsJson();
        $I->dontSeeResponseJsonMatchesJsonPath('data[*]');
        $I->seeResponseContainsJson(
            [
                'message' => 'The given data was invalid.',
                'errors'  => [
                    'name' => ['The name has already been taken.']
                ]
            ]
        );
    }

    public function cantChangeMyEmailToAlreadyTaken(FunctionalTester $I)
    {
        $user  = $I->loginAsUser();
        $user2 = $I->haveUser();

        $I->sendPATCH(apiUrl('users/me'),
            [
                'name'  => $user->name,
                'email' => $user2->email,
            ]
        );

        $I->seeResponseCodeIs(422);
        $I->seeResponseIsJson();
        $I->dontSeeResponseJsonMatchesJsonPath('data[*]');
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
