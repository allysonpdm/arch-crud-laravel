<?php

namespace ArchCrudLaravel\App\Http\Resources\Tests;

use ArchCrudLaravel\App\Http\Resources\BaseResource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\JsonResource;

class TestResource extends BaseResource
{
    protected $route = 'test';
    protected null|int|string $id = null;
    protected mixed $key;
    protected mixed $value;
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
                'chave' => $this->key,
                'valor' => $this->value,
                '@ativo' => empty($this->{$this->resource::DELETED_AT}),
                '@url' => "/{$this->route}/{$this->id}",
            ],
        ];
    }
}
