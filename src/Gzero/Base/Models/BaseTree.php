<?php namespace Gzero\Base\Models;

use Gzero\Base\Traits\DatesFormatTrait;
use Gzero\EloquentTree\Model\Tree;

abstract class BaseTree extends Tree {

    use DatesFormatTrait;

    /**
     * The storage format of the model's date columns.
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    /**
     * Check if file exists
     *
     * @param int $entityId file id
     *
     * @return boolean
     */
    public static function checkIfExists($entityId): bool
    {
        return self::where('id', $entityId)->exists();
    }
}
