<?php namespace Base;

use Codeception\Test\Unit;
use Gzero\Base\UrlParamsProcessor;

class UrlParamsProcessorTest extends Unit {

    /**
     * @var UnitTester
     */
    protected $tester;

    /**
     * @var UrlParamsProcessor
     */
    protected $processor;

    protected function _before()
    {
        $this->processor = $this->initClass();

    }

    /**
     * @test
     */
    public function is_instantiable()
    {
        $this->tester->assertInstanceOf(UrlParamsProcessor::class, $this->processor);
    }

    /**
     * @test
     */
    public function can_filter_params()
    {
        $this->tester->assertEquals(
            $this->processor->getFilterParams(),
            [
                ['lang', '=', 'en'],
                ['test2', '=', 'test2'],
                ['translation.lang_code', '=', 'en']
            ]
        );
    }

    /**
     * @test
     */
    public function can_process_search_query()
    {
        $this->tester->assertEquals(
            $this->processor->getSearchQuery(),
            'Lore Ipsum'
        );
    }

    /**
     * @test
     */
    public function is_returning_page_params()
    {
        $this->tester->assertEquals($this->processor->getPage(), 3);
        $this->tester->assertEquals($this->processor->getPerPage(), 21);
    }

    /**
     * @test
     */
    public function can_process_sort_params()
    {
        $this->tester->assertEquals(
            $this->processor->getOrderByParams(),
            [
                ['test1', 'DESC'],
                ['test2', 'ASC'],
                ['author.created_at', 'ASC'],
            ]
        );
    }

    /**
     * @test
     */
    public function is_returning_processed_fields_in_correct_format()
    {
        $this->tester->assertEquals(
            $this->processor->getProcessedFields(),
            [
                'page'    => 3,
                'perPage' => 21,
                'filter'  => [
                    ['lang', '=', 'en'],
                    ['test2', '=', 'test2'],
                    ['translation.lang_code', '=', 'en']
                ],
                'orderBy' => [
                    ['test1', 'DESC'],
                    ['test2', 'ASC'],
                    ['author.created_at', 'ASC'],
                ],
                'query'   => 'Lore Ipsum'
            ]
        );
    }

    /**
     * @return UrlParamsProcessor
     */
    protected function initClass()
    {
        return (new UrlParamsProcessor())->process(
            [
                'sort'                  => '-test1,test2,author.createdAt',
                'page'                  => 3,
                'per_page'              => 21,
                'lang'                  => 'en',
                'test2'                 => 'test2',
                'translation.lang_code' => 'en',
                'q'                     => 'Lore Ipsum'
            ]
        );
    }
}
