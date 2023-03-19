<?php

namespace ArchCrudLaravel\App\Services\Traits;

use Exception;
use Illuminate\Http\Response;

trait Show
{
    protected $nameResource;
    protected $model;
    protected $request;
    protected $relationships = [];

    use TransactionControl, ExceptionTreatment, ShowRegister;

    public function show(array $request, string|int $id): Response
    {
        try {
            $this->request = $request;
            $response = $this->transaction()
                ->beforeSelect()
                ->select($id)
                ->afterSelect()
                ->commit()
                ->showRegister($id);
            return response($response, 200);
        } catch (Exception $exception) {
            return $this->exceptionTreatment($exception);
        }
    }

    protected function beforeSelect()
    {
        return $this;
    }

    protected function select(string|int $id)
    {
        $this->model = $this->model::findOrFail($id);
        return $this;
    }

    protected function afterSelect()
    {
        return $this;
    }
}
