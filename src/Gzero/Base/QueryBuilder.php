<?php namespace Gzero\Base;

use Illuminate\Database\Eloquent\Builder;

class QueryBuilder {

    /**
     * Default number of items per page
     */
    const ITEMS_PER_PAGE = 20;

    /**
     * @var array
     */
    protected $relations = [];

    /**
     * @var array
     */
    protected $filters = [];

    /**
     * @var array
     */
    protected $sorts = [];

    /**
     * @var string
     */
    protected $searchQuery;

    /**
     * @var int
     */
    protected $pageSize;

    /**
     * Query constructor.
     *
     * @param array $filters  Criteria array
     * @param array $sorts    Order by array
     * @param null  $search   Search query
     * @param int   $pageSize Page size
     */
    public function __construct(array $filters = [], array $sorts = [], $search = null, $pageSize = self::ITEMS_PER_PAGE)
    {
        $this->searchQuery = $search;
        $this->pageSize    = $pageSize;
    }

    public function where($key, $operation, $value)
    {
        if (str_contains($key, '.')) {
            $fullPath            = explode('.', $key);
            $relationPath        = implode('.', array_slice($fullPath, 0, -1));
            $relationKey         = last($fullPath);
            $result              = array_get($this->relations, $relationPath, ['filters' => [], 'sort' => []]);
            $result['filters'][] = new Condition($relationKey, $operation, $value);
            array_set($this->relations, $relationPath, $result);
        } else {
            $this->filters[] = new Condition($key, $operation, $value);
        }
        return $this;
    }

    public function orderBy($key, $direction)
    {
        if (str_contains($key, '.')) {
            $fullPath         = explode('.', $key);
            $relationPath     = implode('.', array_slice($fullPath, 0, -1));
            $relationKey      = last($fullPath);
            $result           = array_get($this->relations, $relationPath, ['filters' => [], 'sort' => []]);
            $result['sort'][] = new OrderBy($relationKey, $direction);
            array_set($this->relations, $relationPath, $result);
        } else {
            $this->sorts[] = new OrderBy($key, $direction);
        }
        return $this;
    }

    public function getFilters(): array
    {
        return $this->filters;
    }

    public function getSorts(): array
    {
        return $this->sorts;
    }

    public function hasRelation($name): bool
    {
        return array_has($this->relations, $name);
    }

    public function getRelationCondition($relationName, $conditionName)
    {
        return array_first($this->relations[$relationName]['filters'], function ($condition) use ($conditionName) {
            return $condition->getName() === $conditionName;
        });
    }

    public function getRelationSort($relationName, $sortName)
    {
        return array_first($this->relations[$relationName]['sorts'], function ($sort) use ($sortName) {
            return $sort->getName() === $sortName;
        });
    }

    public function getRelationFilters($name): array
    {
        return array_get($this->relations, $name . '.filters', []);
    }

    public function getRelationSorts($name): array
    {
        return array_get($this->relations, $name . '.sorts', []);
    }

    public function applyFilters($query)
    {
        foreach ($this->getFilters() as $filter) {
            $filter->apply($query);
        }
    }

    public function applyRelationFilters(string $relationName, string $alias, Builder $query)
    {
        foreach ($this->getRelationFilters($relationName) as $filter) {
            $filter->apply($query, $alias);
        }
    }

    public function applySorts($query)
    {
        foreach ($this->getSorts() as $sort) {
            $sort->apply($query);
        }
    }

    public function applyRelationSorts(string $relationName, string $alias, Builder $query)
    {
        foreach ($this->getRelationSorts($relationName) as $sorts) {
            $sorts->apply($query, $alias);
        }
    }

    /**
     * It resets query builder
     *
     * @return void
     */
    public function reset()
    {
        $this->filters     = collect();
        $this->sorts       = collect();
        $this->searchQuery = null;
        $this->pageSize    = self::ITEMS_PER_PAGE;
    }

    /**
     * It sets search query
     *
     * @param string $search Search string
     *
     * @return void
     */
    public function setSearchQuery(string $search)
    {
        $this->searchQuery = $search;
    }

    /**
     * Set page size
     *
     * @param int $pageSize Page size
     *
     * @return void
     */
    public function setPageSize(int $pageSize)
    {
        $this->pageSize = $pageSize;
    }

    /**
     * Checks if search query is present
     *
     * @return bool
     */
    public function hasSearchQuery()
    {
        return (bool) $this->searchQuery;
    }

    /**
     * Get search query
     *
     * @return string
     */
    public function getSearchQeury()
    {
        return $this->searchQuery;
    }

    /**
     * Get page size
     *
     * @return int
     */
    public function getPageSize()
    {
        return $this->pageSize;
    }

    /**
     * Query factory method
     *
     * @param array $filters  Criteria array
     * @param array $sorts    Order by array
     * @param null  $search   Search query
     * @param int   $pageSize Page size
     *
     * @return QueryBuilder
     */
    public static function with(array $filters = [], array $sorts = [], $search = null, $pageSize = self::ITEMS_PER_PAGE)
    {
        return new self($filters, $sorts, $search, $pageSize);
    }

    /**
     * Query factory method
     *
     * @param array $sorts    Order by array
     * @param null  $search   Search query
     * @param int   $pageSize Page size
     *
     * @return QueryBuilder
     */
    public static function withSort(array $sorts = [], $search = null, $pageSize = self::ITEMS_PER_PAGE)
    {
        return new self([], $sorts, $search, $pageSize);
    }

    /**
     * Query factory method
     *
     * @param null $search   Search query
     * @param int  $pageSize Page size
     *
     * @return QueryBuilder
     */
    public static function withSearch($search = null, $pageSize = self::ITEMS_PER_PAGE)
    {
        return new self([], [], $search, $pageSize);
    }

    /**
     * Query factory method
     *
     * @param int $pageSize Page size
     *
     * @return QueryBuilder
     */
    public static function withPageSize($pageSize = self::ITEMS_PER_PAGE)
    {
        return new self([], [], null, $pageSize);
    }
}
