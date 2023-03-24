<?php

namespace ArchCrudLaravel\App\Services\Traits;

use Illuminate\Database\Eloquent\{
    Builder,
    Model
};

trait ShowRegister
{
    protected $nameResource;
    protected Model|Builder|null $model;
    protected array $request;
    protected array $relationships = [];


    protected function showRegister($id = null)
    {
        if (empty($id)) {
            $id = $this->model->id ?? $this->model::where($this->request);
        }

        $register = $this->model::with($this->relationships)->findOrFail($id);

        return empty($this->nameResource)
            ? $register
            : new $this->nameResource($register);
    }
}
