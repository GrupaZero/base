<?php namespace Gzero\Base\Transformer;

use Gzero\Base\Model\Option;

class OptionTransformer extends AbstractTransformer {

    /**
     * Transforms option entity
     *
     * @param Option|array $option Option entity
     *
     * @return array
     */
    public function transform($option)
    {
        $option = $this->entityToArray(Option::class, $option);
        return $option;
    }
}
