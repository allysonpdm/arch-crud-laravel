<?php

namespace ArchCrudLaravel\App\Services\Traits;

use Illuminate\Database\Eloquent\{
    Builder,
    Model
};

trait ShowRegister
{
    protected ?string $nameModel;
    protected ?string $nameResource;
    protected mixed $model;
    protected array $request;
    protected array $relationships = [];


    protected function showRegister($id = null)
    {
        if (empty($id)) {
            $id = $this->model->id ?? $this->model::where($this->request)->first()->id;
        }

        $register = $this->model::with($this->relationships)->findOrFail($id);

        return empty($this->nameResource)
            ? $register
            : (new $this->nameResource($register))->toArray($register);
    }
}
