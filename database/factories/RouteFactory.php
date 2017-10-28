<?php

use Faker\Generator as Faker;
use Gzero\Base\Models\Language;
use Gzero\Base\Models\Routable;
use Gzero\Base\Models\Route;
use Gzero\Base\Models\RouteTranslation;
use Illuminate\Http\Response;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(Route::class, function (Faker $faker) {
    return [
        'routable_id'   => null,
        'routable_type' => null
    ];
});

$factory->state(Route::class, 'translationEn', function (Faker $faker) {
    return [
        'translations' => function () {
            return factory(RouteTranslation::class, 1)
                ->make(['language_code' => 'en']);
        },
    ];
});

$factory->state(Route::class, 'inactiveTranslationEn', function (Faker $faker) {
    return [
        'translations' => function () {
            return factory(RouteTranslation::class, 1)
                ->states('inactive')
                ->make(['language_code' => 'en']);
        },
    ];
});

$factory->state(Route::class, 'routableHelloWorld', function (Faker $faker) {
    return [
        'routable' => function () {
            return new class implements Routable {
                public function handle(Route $route, Language $lang): Response
                {
                    return response('Hello World');
                }
            };
        }
    ];
});
