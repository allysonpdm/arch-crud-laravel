<?php

namespace ArchCrudLaravel\App\Services\Traits;

use ArchCrudLaravel\App\Exceptions\SoftDeleteException;
use Exception;
use Illuminate\Database\Eloquent\{
    Builder,
    Collection,
    Model
};
use Illuminate\Database\Eloquent\Relations\{
    BelongsTo,
    BelongsToMany,
    HasMany,
    HasManyThrough,
    HasOne,
    HasOneThrough,
    MorphMany,
    MorphOne,
    MorphTo,
    MorphToMany
};
use Illuminate\Http\Response;

trait Destroy
{
    protected ?string $nameResource;
    protected mixed $model;
    protected array $request;
    protected array $relationships = [];
    protected array $ignoreTypesOfRelationships = [];
    protected array $ignoreRelationships = [];
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
        if (
            !self::isActive($register, $this->model::DELETED_AT) &&
            !$force
        ) {
            throw new SoftDeleteException;
        }
        $this->model = self::hasRelationships($register)
            ? $this->softOrHardDelete(
                register: $register,
                force: $force
            )
            : $register->delete();
        return $this;
    }

    protected function softOrHardDelete(Model $register, bool $force = false)
    {
        if ($force) {
            return self::hardDelete(
                register: $register,
                ignoreTypesOfRelationships: $this->ignoreTypesOfRelationships,
                ignoreRelationships: $this->ignoreRelationships
            );
        }

        return self::softDelete($register, $this->model::DELETED_AT, $this->now);
    }

    protected static function hardDelete(
        Model $register,
        array $ignoreTypesOfRelationships = [],
        array $ignoreRelationships = []
    )
    {
        $relations = self::getRelationshipNames(
            model:$register,
            ignoreTypes: $ignoreTypesOfRelationships,
            ignoreRelationships: $ignoreRelationships
        );
        foreach ($relations as $relationName) {
            $relation = $register->{$relationName}();
            self::removeRelations($relation, $register->{$relationName});
        }
        return $register->delete();
    }

    protected static function removeRelations($relation, $register){
        $type = get_class($relation);
        switch ($type) {
            case BelongsTo::class:
            case BelongsToMany::class:
            case MorphTo::class:
            case MorphOne::class:
            case MorphToMany::class:
            case MorphedByMany::class:
            case HasOneThrough::class:
                if ($relation->exists()) {
                    $relation->dissociate();
                }
                break;
            case Collection::class:
            case MorphMany::class:
            case HasManyThrough::class:
            case HasOneOrManyThrough::class:
                if ($relation->isNotEmpty()) {
                    $relation->detach();
                }
                break;
            case HasOne::class:
            case HasMany::class:
                if($register instanceof Model){
                    self::hardDelete($register);
                }
                $relation->delete();
                break;
            default:
        }
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
