<?php namespace Gzero\Base\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

/**
 * @SWG\Definition(
 *   definition="Option",
 *   type="object",
 *   required={"key", "value", "category_key"},
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
class Option extends Resource {

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request request
     *
     * @return array
     */
    public function toArray($request)
    {
        return $this->resource;
    }
}
