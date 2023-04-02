<?php

namespace ArchCrudLaravel\App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Database\Eloquent\{
    Collection,
};

class BaseCollection extends ResourceCollection
{
    protected Collection $collection;
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return $this->collection;
    }
}
