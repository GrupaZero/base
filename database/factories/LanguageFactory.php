<?php

use Faker\Generator as Faker;
use Gzero\Base\Model\Language;

$factory->define(Language::class, function(Faker $faker) {
    do {
        $languageCode = $faker->unique()->languageCode;
    } while (in_array($languageCode, ['en', 'pl', 'de'], true));

    return [
        'code'       => $languageCode,
        'i18n'       => $languageCode . '_' . strtoupper($languageCode),
        'is_enabled' => false,
        'is_default' => false
    ];
});
