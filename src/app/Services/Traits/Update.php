<?php

namespace ArchCrudLaravel\App\Services\Traits;

use ArchCrudLaravel\App\Exceptions\UpdateException;
use Exception;
use Illuminate\Http\Response;

trait Update
{
    protected $nameResource;
    protected $model;
    protected $request;

    use TransactionControl, ExceptionTreatment, ShowRegister;

    public function update(array $request, string|int $id): Response
    {
        $this->request = $request;
        try {
            $response = $this->transaction()
                ->beforeModify()
                ->modify($id)
                ->afterModify()
                ->commit()
                ->showRegister($request['id'] ?? $id);
            return response($response, 200);
        } catch (Exception $exception) {
            return $this->exceptionTreatment($exception);
        }
    }

    protected function beforeModify()
    {
        return $this;
    }

    protected function modify(string|int $id)
    {
        try {
            if (empty($this->request)) {
                throw new UpdateException;
            }
            $this->model = $this->model->findOrFail($id);
            $this->model->update($this->request);
            return $this;
        } catch (Exception $exception) {
            return $this->exceptionTreatment($exception);
        }
    }

    protected function afterModify()
    {
        return $this;
    }
}
