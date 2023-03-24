<?php

namespace ArchCrudLaravel\App\Services\Traits;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Response;
trait Show
{
    protected string $nameResource;
    protected Model $model;
    protected array $request;
    protected array $relationships = [];

    use TransactionControl, ExceptionTreatment, ShowRegister;

    public function show(array $request, string|int $id): Response
    {
        try {
            $this->request = $request;
            $cacheKey = $this->createCacheKey(id: $id);

            $response = $this->getCache(key: $cacheKey) ?? $this->transaction()
                ->beforeSelect()
                ->select($id)
                ->afterSelect()
                ->commit()
                ->showRegister($id);
            
            $this->putCache(
                key: $cacheKey,
                value: $response
            );
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
