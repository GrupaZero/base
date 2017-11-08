<?php namespace Gzero\Base;

use Gzero\Base\Http\Filters\QueryFilter;
use Illuminate\Contracts\Validation\Factory;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class NewUrlParamsProcessor {

    /**
     * @var int
     */
    protected $page = 1;

    /**
     * @var int
     */
    protected $perPage = QueryBuilder::ITEMS_PER_PAGE;

    /**
     * @var Collection
     */
    protected $filters;

    /**
     * @var array
     */
    protected $filterDefinitions = [];

    /**
     * @var array
     */
    protected $orderBy = [];

    /**
     * @var null
     */
    protected $searchQuery = null;

    /**
     * @var Factory
     */
    protected $validator;

    /**
     * @var array
     */
    protected $rules = [
        'page'     => 'numeric',
        'per_page' => 'numeric',
        'sort'     => 'string',
        'q'        => 'string',
    ];

    /**
     * UrlParamsProcessor constructor.
     *
     * @param Factory $validator
     */
    public function __construct(Factory $validator)
    {
        $this->validator = $validator;
        $this->filters   = collect();
    }

    /**
     * Returns page number
     *
     * @return int
     */
    public function getPage(): int
    {
        return $this->page;
    }

    /**
     * Returns page number
     *
     * @return int
     */
    public function getPerPage(): int
    {
        return $this->perPage;
    }

    /**
     * Returns orderBy array
     *
     * @return array
     */
    public function getOrderByParams(): array
    {
        return $this->orderBy;
    }

    /**
     *  Returns filters collection
     *
     * @return Collection
     */
    public function getFilters(): Collection
    {
        return $this->filters;
    }

    /**
     *  Returns filter array
     *
     * @return string
     */
    public function getSearchQuery(): string
    {
        return $this->searchQuery;
    }

    /**
     * Returns array with all processed fields
     *
     * @return array
     */
    public function getProcessedFields()
    {
        return [
            'page'    => $this->getPage(),
            'perPage' => $this->getPerPage(),
            'filters' => $this->filters,
            'orderBy' => $this->orderBy,
            'query'   => $this->searchQuery
        ];
    }

    /**
     * @param QueryFilter $filter
     *
     * @return $this
     */
    public function setFilters(QueryFilter $filter) // Interface
    {
        $this->filters = $filter;
        return $this;
    }

    /**
     * Process params
     *
     * @param Request $request Request object
     *
     * @return $this
     */
    public function process(Request $request)
    {
        $rules = $this->rules;
        // Register all validation rules for additional filters
        $this->filters->each(function ($filter) use (&$rules) { // Reference?:( Maybe map
            $rules = array_merge($rules, $filter->getValidationRules());
        });

        $this->validate($request->all(), $rules);

        // Get all default fields like page, per_page, query

        // Handle all custom filters on validated data

        // Return processed data with filter instances

        if ($request->has('q')) {
            $this->searchQuery = $request->get('q');
        }
        if ($request->has('sort')) {
            foreach (explode(',', $request->get('sort')) as $sort) {
                $this->processOrderByParams($sort);
            }
        }
        $this->processPageParams($request);

        $this->filters->each(function ($filter) use ($request) { // Reference?:( Maybe map
            $filter->handle($request);
        });
        return $this;
    }

    /**
     * @param $data
     * @param $rules
     *
     * @return $this
     * @throws ValidationException
     */
    protected function validate($data, $rules)
    {
        $validator = $this->validator->make($data, $rules);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
        return $this;
    }

    /**
     * Process order by params
     *
     * @param string $sort Sort parameter
     *
     * @return void
     */
    protected function processOrderByParams($sort)
    {
        $direction       = (substr($sort, 0, 1) == '-') ? 'DESC' : 'ASC';
        $field           = (substr($sort, 0, 1) == '-') ? substr($sort, 1) : $sort;
        $this->orderBy[] = [$field, $direction];
    }

    /**
     * Process page params
     *
     * @param Request $request Request object
     *
     */
    protected function processPageParams(Request $request)
    {
        if ($request->has('page') && is_numeric($request->get('page'))) {
            $this->page = $request->get('page');
        }
        if ($request->has('per_page') && is_numeric($request->get('per_page'))) {
            $this->perPage = $request->get('per_page');
        }
    }

}
