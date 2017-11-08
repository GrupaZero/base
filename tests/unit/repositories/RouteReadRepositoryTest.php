<?php namespace Base;

use Codeception\Test\Unit;
use Gzero\Base\Models\Route;
use Gzero\Base\Models\RouteTranslation;
use Gzero\Base\QueryBuilder;
use Gzero\Base\Repositories\RouteReadRepository;
use Gzero\Base\Services\RepositoryException;

class RouteReadRepositoryTest extends Unit {

    /** @var UnitTester */
    protected $tester;

    /** @var RouteReadRepository */
    protected $repository;

    protected function _before()
    {
        $this->repository = new RouteReadRepository();
    }

    /** @test */
    public function canAddConditionsToGetMany()
    {
        factory(Route::class, 5)->create();
        factory(Route::class, 2)->create()
            ->each(function ($route) {
                $route->translations()
                    ->save(
                        factory(RouteTranslation::class)
                            ->make(['language_code' => 'en'])
                    );
                $route->translations()
                    ->save(
                        factory(RouteTranslation::class)
                            ->states('inactive')
                            ->make(['language_code' => 'pl'])
                    );
            });

        $result = $this->repository->getMany(
            (new QueryBuilder)
                ->where('translations.is_active', '=', true)
                ->where('translations.language_code', '=', 'en')
                ->orderBy('id', 'asc')
        );

        $this->assertEquals(2, $result->count());
        $this->assertEquals('en', $result[0]->translations[0]->language_code);
        $this->assertEquals('en', $result[1]->translations[0]->language_code);
    }

    /** @test */
    public function shouldCheckDependantField()
    {
        factory(Route::class, 2)->create()
            ->each(function ($route) {
                $route->translations()
                    ->save(
                        factory(RouteTranslation::class)
                            ->make(['language_code' => 'en'])
                    );
                $route->translations()
                    ->save(
                        factory(RouteTranslation::class)
                            ->make(['language_code' => 'pl'])
                    );
            });

        try {
            $this->repository->getMany(
                (new QueryBuilder)
                    ->where('translations.is_active', '=', true)
                    ->orderBy('id', 'asc')
            );
        } catch (RepositoryException $exception) {
            $this->assertEquals('Language code is required', $exception->getMessage());
            return;
        }

        $this->fail('Exception should be thrown');
    }
}

