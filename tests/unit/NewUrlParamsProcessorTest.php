<?php namespace Base;

use Codeception\Test\Unit;
use Gzero\Base\Condition;
use Gzero\Base\NewUrlParamsProcessor;
use Gzero\Base\OrderBy;
use Gzero\Base\StringParser;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class NewUrlParamsProcessorTest extends Unit {

    /**
     * @var UnitTester
     */
    protected $tester;

    /**
     * @var NewUrlParamsProcessor
     */
    protected $processor;

    public function _before()
    {
        $this->processor = new NewUrlParamsProcessor(resolve('Illuminate\Contracts\Validation\Factory'));
    }

    /** @test */
    public function isInstantiable()
    {
        $this->tester->assertInstanceOf(NewUrlParamsProcessor::class, $this->processor);
    }

    /** @test */
    public function canRegisterParsers()
    {
        $this->processor
            ->addFilter(new StringParser('translations.url'))
            ->addFilter(new StringParser('translations.language_code'))
            ->process(new Request([
                'sort'         => '-test1,test2,author.createdAt',
                'page'         => 3,
                'per_page'     => 21,
                'translations' => [
                    'language_code' => 'en',
                    'url'           => 'awesome-url'
                ]
            ]));
    }

    /** @test */
    public function shouldMergeValidationRulesFromFilter()
    {
        try {
            $this->processor
                ->addFilter(new StringParser('translations.language_code'), 'required_with:translations.url|string')
                ->process(new Request([
                    'sort'         => '-test1,test2,author.createdAt',
                    'page'         => 3,
                    'per_page'     => 21,
                    'translations' => [
                        'url' => 'awesome-url'
                    ]
                ]));
        } catch (ValidationException $exception) {
            $this->assertEquals(
                [
                    'translations.language_code' => [
                        'The translations.language code field is required when translations.url is present.'
                    ]
                ],
                $exception->errors());
            return;
        }
        $this->fail('Exception should be thrown');
    }

    /** @test */
    public function shouldReturnParsers()
    {
        $this->processor
            ->addFilter(new StringParser('translations.url'))
            ->process(new Request([
                'sort'         => '-test1,test2,author.createdAt',
                'page'         => 3,
                'per_page'     => 21,
                'translations' => [
                    'language_code' => 'en',
                    'url'           => 'awesome-url'
                ]
            ]));

        $this->tester->assertCount(1, $this->processor->getParsers());
        $this->tester->assertInstanceOf(StringParser::class, $this->processor->getParsers()[0]);
    }

    /** @test */
    public function canProcessSearchQuery()
    {
        $this->processor->process(new Request(['q' => 'Lore Ipsum']));

        $this->tester->assertEquals(
            $this->processor->getSearchQuery(),
            'Lore Ipsum'
        );
    }

    /** @test */
    public function isReturningPageParams()
    {
        $this->processor->process(new Request([
            'page'     => 3,
            'per_page' => 21
        ]));

        $this->tester->assertEquals($this->processor->getPage(), 3);
        $this->tester->assertEquals($this->processor->getPerPage(), 21);
    }

    /** @test */
    public function shouldReturnQueryBuilderWithCorrectOrderBy()
    {
        $this->processor->process(new Request(['sort' => '-test1,test2,author.created_at']));

        $query = $this->processor->buildQueryBuilder();

        $this->tester->assertEquals([new OrderBy('test1', 'DESC'), new OrderBy('test2', 'ASC')], $query->getSorts());
        $this->tester->assertEquals([new OrderBy('created_at', 'ASC')], $query->getRelationSorts('author'));
        $this->tester->assertEquals(new OrderBy('created_at', 'ASC'), $query->getRelationSort('author', 'created_at'));
    }

    /** @test */
    public function shouldReturnQueryBuilderWithCorrectFilters()
    {
        $this->processor
            ->addFilter(new StringParser('not_required_filter'))
            ->addFilter(new StringParser('lang'))
            ->addFilter(new StringParser('test2'), 'required')
            ->addFilter(new StringParser('translation.language_code'))
            ->process(new Request([
                'lang'                      => 'en',
                'test2'                     => 'test2',
                'translation.language_code' => 'en',
            ]));
        $query = $this->processor->buildQueryBuilder();

        $this->assertEquals(
            [new Condition('lang', '=', 'en'), new Condition('test2', '=', 'test2')],
            $query->getFilters()
        );
        $this->assertEquals(
            [new Condition('language_code', '=', 'en')],
            $query->getRelationFilters('translation')
        );
        $this->assertEquals(
            new Condition('language_code', '=', 'en'),
            $query->getRelationFilter('translation', 'language_code')
        );
    }
}
