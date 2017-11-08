<?php namespace Gzero\Base;

use Illuminate\Database\Eloquent\Builder;

class Condition {

    /** @var string */
    protected $name;

    /** @var string */
    protected $operation;

    /** @var mixed */
    protected $value;

    /** @var array */
    public static $allowedOperations = [
        '=',
        '!=',
        '>',
        '>=',
        '<',
        '<=',
        'in',
        'not in',
        'like',
        'not like',
        'between',
        'not between'
    ];

    /** @var array */
    public static $negateOperators = ['!=', 'not in', 'not between', 'not like'];

    /**
     * Condition constructor.
     *
     * @param string $name
     * @param string $operation
     * @param mixed  $value
     *
     * @throws Exception
     */
    public function __construct(string $name, string $operation, $value)
    {
        if (empty($name)) {
            throw new Exception('Condition: Key must be defined');
        }
        $this->name      = strtolower($name);
        $this->operation = strtolower($operation);
        $this->value     = ($operation === 'in' || $operation === 'not in') ? array_wrap($value) : $value;
        $this->validate();
    }

    public function getName()
    {
        return $this->name;
    }

    public function getOperation()
    {
        return $this->operation;
    }

    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return bool
     */
    public function isNegate(): bool
    {
        return in_array($this->operation, self::$negateOperators, true);
    }

    /**
     * @return bool
     */
    public function isNullCondition(): bool
    {
        return $this->value === null;
    }

    public function apply(Builder $query, string $tableAlias = null)
    {
        $tableAlias = ($tableAlias != null) ? str_finish($tableAlias, '.') : '';

        switch ($this->operation) {
            case '=' :
                $query->where($tableAlias . $this->name, $this->operation, $this->value);
                break;
            case '!=':
                $query->notWhere($tableAlias . $this->name, $this->value);
                break;
            default;
                throw new Exception('Unsupported operation');
        }
    }

    /**
     * @throws Exception
     */
    protected function validate()
    {
        if (!in_array($this->operation, self::$allowedOperations, true)) {
            throw new Exception('Unsupported condition operation');
        }
        if (is_array($this->value) && !$this->isCorrectRangeFormat()) {
            throw new Exception('Wrong number of values for range');
        }
    }

    /**
     * @return bool
     */
    protected function isCorrectRangeFormat(): bool
    {
        return ($this->operation === 'between' || $this->operation === 'not between')
            && count($this->value) !== 2;
    }
}