<?php

namespace ArchCrudLaravel\App\Services\Traits;

use ArchCrudLaravel\App\Enums\Http\StatusCode;
use Exception;
use Illuminate\Database\Eloquent\{
    Builder,
    Model
};
use Illuminate\Http\Response;

trait Show
{
    protected ?string $nameResource;
    protected int|string $id;
    protected mixed $model;
    protected array $request;
    protected array $relationships = [];

    use CacheControl, TransactionControl, ExceptionTreatment, ShowRegister;

    public function show(array $request, string|int $id): Response
    {
        try {
            $this->request = $request;
            $this->id = $id;
            $cacheKey = $this->createCacheKey(id: $this->id, request: $this->request);

            $response = $this->getCache(key: $cacheKey) ?? $this->transaction()
                ->beforeSelect()
                ->select()
                ->afterSelect()
                ->commit()
                ->showRegister($this->id);

            $this->putCache(
                key: $cacheKey,
                value: $response
            );
            return response($response, StatusCode::OK->value);
        } catch (Exception $exception) {
            return $this->exceptionTreatment($exception);
        }
    }

    protected function beforeSelect()
    {
        return $this;
    }

    protected function select()
    {
        $this->model = $this->model::findOrFail($this->id);
        return $this;
    }

    protected function afterSelect()
    {
        return $this;
    }
}
