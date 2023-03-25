<?php

namespace ArchCrudLaravel\App\Services\Traits;

use ArchCrudLaravel\App\Exceptions\UpdateException;
use Exception;
use Illuminate\Http\Response;

trait Update
{
    protected ?string $nameResource;
    protected mixed $model;
    protected array $request;

    use TransactionControl, ExceptionTreatment, ShowRegister, CacheControl;

    public function update(array $request, string|int $id): Response
    {
        $this->request = $request;
        try {
            $cacheKey = $this->createCacheKey(id: $id);
            $response = $this->transaction()
                ->beforeModify()
                ->modify($id)
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
