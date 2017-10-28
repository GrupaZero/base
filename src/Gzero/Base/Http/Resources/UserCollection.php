<?php namespace Gzero\Base\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class UserCollection extends ResourceCollection {

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request request
     *
     * @return \Illuminate\Support\Collection
     */
    public function toArray($request)
    {
        return $this->collection;
    }
}
