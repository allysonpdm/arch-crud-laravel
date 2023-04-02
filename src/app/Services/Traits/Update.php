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

            if (
                array_key_exists($this->model::DELETED_AT, $this->request) &&
                $this->request[$this->model::DELETED_AT] === null &&
                $this->register->{$this->model::DELETED_AT} !== $this->request[$this->model::DELETED_AT]
            ) {
                $this->reactivate();
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

    protected function reactivate()
    {
        if ($this->isModelVisited($this->register)) {
            return;
        }

        $this->markModelVisited($this->register);

        $this->register->update([$this->model::DELETED_AT => null]);
        $relations = self::getRelationshipNames(model: $this->register);

        foreach ($relations as $relationName) {
            $relation = $this->register->{$relationName}();

            if (self::isSupportedRelation($relation)) {
                $relatedItems = $this->register->{$relationName};
                $this->processReactivateOnRelatedItems($relatedItems);
            }
        }
    }

    protected function processReactivateOnRelatedItems($relatedItems)
    {
        if ($relatedItems instanceof Collection || is_array($relatedItems)) {
            foreach ($relatedItems as $related) {
                if ($related !== null && $related instanceof Model && $related->exists) {
                    $this->reactivateRelatedItems($related);
                }
            }
        } elseif ($relatedItems instanceof Model) {
            if ($relatedItems !== null && $relatedItems->exists) {
                $this->reactivateRelatedItems($relatedItems);
            }
        }
    }

    protected function reactivateRelatedItems($relatedItems)
    {
        $originalRegister = $this->register;
        $this->register = $relatedItems;
        $this->reactivate();
        $this->register = $originalRegister;
    }
}
