<?php namespace Gzero\Base\Transformers;

use Gzero\Base\Models\OptionCategory;

/**
 * @SWG\Definition(
 *   definition="OptionCategory",
 *   type="object",
 *   required={"key"},
 *   @SWG\Property(
 *     property="key",
 *     type="string"
 *   )
 * )
 */
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
