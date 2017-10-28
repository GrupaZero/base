<?php namespace Base;

class AdminLanguageCest {

    use AdminApiTest;

    public function getSingleLanguage(FunctionalTester $I)
    {
        $I->sendGet(apiUrl('admin/languages', ['en']));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->dontSeeResponseJsonMatchesJsonPath('data[*]');
        $I->seeResponseContainsJson(
            [
                'code'       => 'en',
                'i18n'       => 'en_US',
                'is_enabled' => true,
                'is_default' => true,
            ]
        );
    }

    public function getLanguages(FunctionalTester $I)
    {
        $I->sendGet(apiUrl('admin/languages'));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('data[*]');
        $I->seeResponseContainsJson(

            [
                'code'       => 'pl',
                'i18n'       => 'pl_PL',
                'is_enabled' => true,
                'is_default' => false
            ],
            [
                'code'       => 'de',
                'i18n'       => 'de_DE',
                'is_enabled' => false,
                'is_default' => false
            ],
            [
                'code'       => 'fr',
                'i18n'       => 'fr_FR',
                'is_enabled' => false,
                'is_default' => false
            ],
            [

                'code'       => 'en',
                'i18n'       => 'en_US',
                'is_enabled' => true,
                'is_default' => true
            ]
        );
    }
}
