<?php namespace Gzero\Base\Transformers;

use Gzero\Base\Models\Option;

/**
 * @SWG\Definition(
 *   definition="Option",
 *   type="object",
 *   required={"key", "value"},
 *   @SWG\Property(
 *     property="key",
 *     type="string"
 *   ),
 *   @SWG\Property(
 *     property="value",
 *     type="string"
 *   ),
 *   @SWG\Property(
 *     property="category_key",
 *     type="string"
 *   )
 * )
 */
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
