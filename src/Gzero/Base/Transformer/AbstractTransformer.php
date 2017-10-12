<?php namespace Gzero\Base\Transformer;

use League\Fractal\TransformerAbstract;

class AbstractTransformer extends TransformerAbstract {

    /**
     * Return entity transformed to array
     *
     * @param string $class  Entity class
     * @param mixed  $object Entity object or array
     *
     * @return array
     */
    protected function entityToArray($class, $object)
    {
        if (is_object($object) && get_class($object) == $class) {
            $object = $object->toArray();
        }
        return $object;
    }
}
