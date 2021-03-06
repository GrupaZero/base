<?php namespace Gzero\Base\Parsers;

use Gzero\Base\Exception;
use Gzero\Base\Query\QueryBuilder;
use Illuminate\Http\Request;

/**
 * @TODO write custom Laravel validator
 * @TODO parse date format to DB format
 * @TODO we should always have two dates
 * @TODO human readable? e.g. -7days,+2days
 */
class DateRangeParser implements ConditionParser {

    /** @var string */
    protected $name;

    /** @var string */
    protected $operation = 'between';

    /** @var array */
    protected $value;

    /** @var bool */
    protected $applied = false;

    /** @var array */
    protected $availableOperations = ['!'];

    /** @var array */
    protected $option;

    /**
     * @param string $name    Field name
     *
     * @param array  $options Optional array of options
     *
     * @throws Exception
     */
    public function __construct(string $name, $options = [])
    {
        if (empty($name)) {
            throw new Exception('DataRangeParser: Name must be defined');
        }
        $this->name   = $name;
        $this->option = $options;
    }

    /**
     * It returns field name
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * It returns operation
     *
     * @return string
     */
    public function getOperation(): string
    {
        return $this->operation;
    }

    /**
     * It returns value
     *
     * @return mixed|null
     */
    public function getValue()
    {
        return ($this->value) ?: null;
    }

    /**
     * Checks if field was present in response during parse phase
     *
     * @return bool
     */
    public function wasApplied(): bool
    {
        return $this->applied;
    }

    /**
     * It parses request field
     *
     * @param Request $request Request object
     *
     * @return void
     */
    public function parse(Request $request)
    {
        if ($request->has($this->name)) {
            $this->applied = true;
            $value         = $request->get($this->name);
            $operation     = substr($value, 0, 1);
            if ($operation === '!') {
                $this->operation = 'not between';
                $this->value     = explode(',', substr($value, 1));
            } else {
                $this->value = explode(',', $value);
            }
        }
    }

    /**
     * It returns validation rules for this type
     *
     * @return string
     */
    public function getValidationRule()
    {
        return 'string';
    }

    /**
     * It returns query builder that can be pass further to read repository
     *
     * @param QueryBuilder $builder Query builder
     *
     * @return void
     */
    public function apply(QueryBuilder $builder)
    {
        $builder->where($this->name, $this->operation, $this->value);
    }

}
