<?php namespace Gzero\Base\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class RoleCollection extends ResourceCollection {

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request request
     *
     * @return array|\Illuminate\Support\Collection
     */
    public function toArray($request)
    {
        return $this->collection;
    }
}
