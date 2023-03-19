<?php

namespace ArchCrudLaravel\App\Services\Traits;

trait ShowRegister
{
    protected $nameResource;
    protected $model;
    protected $request;
    protected $relationships = [];


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
