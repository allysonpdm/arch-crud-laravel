<?php

namespace ArchCrudLaravel\App\Services\Traits;

use Illuminate\Database\Eloquent\{
    Builder,
    Model
};
use \Illuminate\Http\Request;

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

        if($this->nameResource){
            return $register;
        }

        return match(true) {
            $register instanceof Request => (new $this->nameResource($register))->toArray($register),
            default => $register
        };

    }
}
