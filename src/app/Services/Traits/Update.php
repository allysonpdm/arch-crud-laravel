<?php

namespace ArchCrudLaravel\App\Services\Traits;

use ArchCrudLaravel\App\Exceptions\UpdateException;
use Exception;
use Illuminate\Http\Response;
use Illuminate\Database\Eloquent\{
    Builder,
    Collection,
    Model
};

trait Update
{
    protected ?string $nameResource;
    protected int|string $id;
    protected mixed $model;
    protected Model $register;
    protected array $request;

    use TransactionControl, ExceptionTreatment, ShowRegister, CacheControl;

    public function update(array $request, string|int $id): Response
    {
        try {
            $this->request = $request;
            $this->id = $id;
            $this->register = $this->model->findOrFail($this->id);
            $cacheKey = $this->createCacheKey(id: $this->id);
            $response = $this->transaction()
                ->beforeModify()
                ->modify()
                ->afterModify()
                ->commit()
                ->showRegister($request['id'] ?? $id);

            $this->putCache(
                key: $cacheKey,
                value: $response
            );

            return response($response, 200);
        } catch (Exception $exception) {
            return $this->exceptionTreatment($exception);
        }
    }

    protected function beforeModify()
    {
        return $this;
    }

    protected function modify()
    {
        try {
            if (empty($this->request)) {
                throw new UpdateException;
            }
            $this->register->update($this->request);
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
