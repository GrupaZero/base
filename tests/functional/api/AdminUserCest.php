<?php namespace Base;

use Illuminate\Support\Facades\Hash;

class AdminUserCest {

    use AdminApiTest;

    public function getUsers(FunctionalTester $I)
    {
        $usersNumber = 4;
        for ($i = 0; $i < $usersNumber; $i++) {
            $I->haveUser();
        }

        $I->sendGET(apiUrl('admin/users'));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('data[*]');
        $I->seeResponseContainsJson(
            [
                'meta'   => [
                    'total'       => $usersNumber + 1,
                    'perPage'     => 20,
                    'currentPage' => 1,
                    'lastPage'    => 1,
                    'link'        => apiUrl('admin/users'),
                ],
                'params' => [
                    'page'    => 1,
                    'perPage' => 20,
                    'filter'  => [],
                ],
            ]
        );
    }

    public function getSingleUser(FunctionalTester $I)
    {
        $user = $I->haveUser(
            [
                'name'       => 'Test user',
                'first_name' => 'John',
                'last_name'  => 'Doe',
                'password'   => Hash::make('test123')
            ]
        );

        $I->sendGet(apiUrl('admin/users', [$user->id]));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->dontSeeResponseJsonMatchesJsonPath('data[*]');
        $I->seeResponseContainsJson(
            [
                'name'      => 'Test user',
                'first_name' => 'John',
                'last_name'  => 'Doe',
            ]
        );
    }

    public function checksIfUserExistsWhenGetting(FunctionalTester $I)
    {
        $I->sendGET(apiUrl('admin/users', [4]));
        
        $I->seeResponseCodeIs(404);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['message' => "Not found"]);
    }


    public function updateUser(FunctionalTester $I)
    {
        $user = $I->haveUser();

        $I->sendPATCH(apiUrl('admin/users', [$user->id]),
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
                'name'      => 'Modified user',
                'first_name' => 'Johny',
                'last_name'  => 'Stark',
                'email'     => $user->email,
            ]
        );
    }

    public function checksIfUserExistsWhenUpdating(FunctionalTester $I)
    {
        $I->sendPATCH(apiUrl('admin/users', [4]));

        $I->seeResponseCodeIs(404);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['message' => "Not found"]);
    }

    public function deleteUser(FunctionalTester $I)
    {
        $user = $I->haveUser();

        $I->sendDELETE(apiUrl('admin/users', [$user->id]));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['success' => true]
        );
    }

}
