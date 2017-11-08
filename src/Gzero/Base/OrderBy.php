<?php namespace Gzero\Base;

use Illuminate\Database\Eloquent\Builder;

class OrderBy {

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $direction;

    /**
     * @var array
     */
    public static $allowedOperations = [
        'asc',
        'desc'
    ];

    /**
     * OrderBy constructor.
     *
     * @param string $name
     * @param string $direction
     *
     * @throws Exception
     */
    public function __construct(string $name, string $direction)
    {
        if (empty($name)) {
            throw new Exception('OrderBy: Name must be defined');
        }
        $this->name      = strtolower($name);
        $this->direction = strtolower($direction);
        $this->validate();
    }

    public function getName()
    {
        return $this->name;
    }

    public function getDirection()
    {
        return $this->direction;
    }

    public function apply(Builder $query, $tableAlias = null)
    {
        $tableAlias = ($tableAlias != null) ? str_finish($tableAlias, '.') : '';
        $query->orderBy($tableAlias . $this->name, $this->direction);
    }

    /**
     * @throws Exception
     */
    protected function validate()
    {
        //if (!in_array($this->operation, self::$allowedOperations, true)) {
        //    throw new Exception('Unsupported condition operation');
        //}
        //if (is_array($this->value) && !$this->isCorrectRangeFormat()) {
        //    throw new Exception('Wrong number of values for range');
        //}
    }

}