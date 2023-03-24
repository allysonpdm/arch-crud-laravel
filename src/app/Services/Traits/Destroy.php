<?php

namespace ArchCrudLaravel\App\Services\Traits;

use ArchCrudLaravel\App\Exceptions\SoftDeleteException;
use Exception;
use Illuminate\Database\Eloquent\{
    Builder,
    Model
};
use Illuminate\Http\Response;

trait Destroy
{
    protected ?string $nameResource;
    protected mixed $model;
    protected array $request;
    protected array $relationships = [];
    protected string $now;

    use TransactionControl, ExceptionTreatment, Relationships, CacheControl;

    public function destroy(array $request, string|int $id): Response
    {
        $this->request = $request;
        try {
            $cacheKey = $this->createCacheKey(id: $id);
            $this->forgetCache(key: $cacheKey);
            $response = $this->transaction()
                ->beforeDelete()
                ->delete($id)
                ->afterDelete()
                ->commit()
                ->model;

            return response($response, 200);
        } catch (Exception $exception) {
            return $this->exceptionTreatment($exception);
        }
    }

    protected function beforeDelete()
    {
        return $this;
    }

    protected function delete(string|int $id)
    {
        $force = $this->request['force'] ?? false;
        $register = $this->model->findOrFail($id);
        if (!self::isActive($register, $this->model::DELETED_AT)) {
            throw new SoftDeleteException;
        }
        $this->model = self::hasRelationships($register)
            ? $this->softOrHardDelete($force, $register)
            : $register->delete();
        return $this;
    }

    protected function softOrHardDelete($force, $register)
    {
        if ($force) {
            return self::hardDelete($register);
        }

        return self::softDelete($register, $this->model::DELETED_AT, $this->now);
    }

    protected static function hardDelete($register)
    {
        $relations = self::getRelationships($register);
        foreach ($relations as $relationName) {
            if (!empty($register->{$relationName}) && $register->{$relationName}->count() > 0) {
                $relation = $register->{$relationName}();
                if (method_exists($relation, 'dissociate')) {
                    $relation->dissociate();
                }
                if (method_exists($relation, 'detach')) {
                    $relation->detach();
                }
            }
        }
        return $register->delete();
    }

    protected static function softDelete(Model $register, string $nameColumn, string $value): bool
    {
        return $register->update([$nameColumn => $value]);
    }

    protected static function isActive(Model $register, string $nameColumn): bool
    {
        return empty($register->{$nameColumn});
    }

    protected function afterDelete()
    {
        return $this;
    }
}
