<?php namespace Base;

class AdminOptionsCest {

    use AdminApiTest;

    public function getOptionsCategories(FunctionalTester $I)
    {
        $I->sendGET(apiUrl('admin/options'));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(
            [
                'data' => [
                    ['key' => 'general'],
                    ['key' => 'seo']
                ]
            ]
        );
    }

    public function getOptionsFromGivenCategory(FunctionalTester $I)
    {
        $I->sendGET(apiUrl('admin/options/seo'));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(
            [
                'google_analytics_id' =>
                    [
                        'en' => null,
                        'pl' => null,
                        'de' => null,
                        'fr' => null,
                    ],
                'desc_length'         =>
                    [
                        'en' => 160,
                        'pl' => 160,
                        'de' => 160,
                        'fr' => 160,
                    ],
            ]
        );
    }

    public function updateOptionValue(FunctionalTester $I)
    {
        $I->sendPUT(apiUrl('admin/options/seo'),
            [
                'key'   => 'desc_length',
                'value' => [
                    'en' => 160,
                    'pl' => 161,
                    'de' => 162,
                    'fr' => 163,
                ],
            ]
        );

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(
            [
                'google_analytics_id' =>
                    [
                        'en' => null,
                        'pl' => null,
                        'de' => null,
                        'fr' => null,
                    ],
                'desc_length'         =>
                    [
                        'en' => 160,
                        'pl' => 161,
                        'de' => 162,
                        'fr' => 163,
                    ],
            ]
        );
    }

    public function getOptionsFromNonExistingCategory(FunctionalTester $I)
    {
        $I->sendGET(apiUrl('admin/options/some_category'));

        $I->seeResponseCodeIs(400);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(
            [
                'message' => 'Category some_category does not exist',
            ]
        );
    }

    public function updateNonExistingOption(FunctionalTester $I)
    {
        $I->sendPUT(apiUrl('admin/options/seo'),
            [
                'key'   => 'not_an_option',
                'value' => [
                    ['lorem' => 'ipsum']
                ],
            ]
        );

        $I->seeResponseCodeIs(422);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(
            [
                'message' => 'The given data was invalid.',
                'errors'  => [
                    'key' => [
                        0 => 'The selected key is invalid.',
                    ],
                ],
            ]

        );
    }

    public function updateOptionInNonExistingCategory(FunctionalTester $I)
    {
        $I->sendPUT(apiUrl('admin/options/some_category'),
            [
                'key'   => 'not_an_option',
                'value' => [
                    ['lorem' => 'ipsum']
                ],
            ]
        );

        $I->seeResponseCodeIs(422);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(
            [
                'message' => 'The given data was invalid.',
                'errors'  => [
                    'key' => [
                        0 => 'The selected key is invalid.',
                    ],
                ],
            ]

        );
    }
}
