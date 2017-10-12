<?php namespace Gzero\Base\Transformer;

use Gzero\Base\Model\OptionCategory;

class OptionCategoryTransformer extends AbstractTransformer {

    /**
     * Transforms option category entity
     *
     * @param array $option OptionCategory entity
     *
     * @return array
     */
    public function transform($option)
    {
        $options = $this->entityToArray(OptionCategory::class, $option);
        $data    = ['data' => []];
        foreach ($options as $option) {
            $data['data'][] = [
                'key' => $option,
            ];
        }

        return $data;
    }
}
