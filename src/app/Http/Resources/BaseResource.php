<?php

namespace ArchCrudLaravel\App\Http\Resources;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\JsonResource;

abstract class BaseResource extends JsonResource
{
    protected $route;
    protected null|int|string $id = null;
    public $resource;

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $data = parent::toArray($request);

        return [
            ...self::sanitize($data, [
                $this->resource::DELETED_AT
            ]),
            ...[
                '@ativo' => empty($this->{$this->resource::DELETED_AT}),
                '@url' => "/{$this->route}/{$this->id}",
            ],
        ];
    }

    protected static function sanitize(array $data, array $keysRemove): array
    {
        return array_diff_key($data, array_flip($keysRemove));
    }
}
