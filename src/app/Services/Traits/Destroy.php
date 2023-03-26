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
    protected int|string $id;
    protected mixed $model;
    protected Model $register;
    protected bool $force;
    protected array $request;
    protected array $relationships = [];
    protected array $ignoreTypesOfRelationships = [];
    protected array $ignoreRelationships = [];
    protected string $now;

    use TransactionControl, ExceptionTreatment, Relationships, CacheControl;

    public function destroy(array $request, string|int $id): Response
    {
        try {
            $this->request = $request;
            $this->id = $id;
            $this->register = $this->model->findOrFail($this->id);
            $this->force = $this->request['force'] ?? false;
            $cacheKey = $this->createCacheKey(id: $id);
            $this->forgetCache(key: $cacheKey);
            $response = $this->transaction()
                ->beforeDelete()
                ->delete($id)
                ->afterDelete()
                ->commit()
                ->model;

            $response = ($response && $this->force)
                ? 'O registro e os vínculos foram excluídos definitivamente.'
                : 'O registro foi desabilitado.';
            return response($response, 200);
        } catch (Exception $exception) {
            return $this->exceptionTreatment($exception);
        }
    }

    protected function beforeDelete()
    {
        return $this;
    }

    protected function delete()
    {
        if (
            !self::isActive($this->register, $this->model::DELETED_AT) &&
            !$this->force
        ) {
            throw new SoftDeleteException;
        }
        $this->model = self::hasRelationships($this->register)
            ? $this->softOrHardDelete(
                register: $this->register
            )
            : $this->register->delete();
        return $this;
    }

    protected function softOrHardDelete(Model $register)
    {
        if ($this->force) {
            return self::hardDelete(
                register: $register,
                ignoreTypesOfRelationships: $this->ignoreTypesOfRelationships,
                ignoreRelationships: $this->ignoreRelationships
            );
        }
        $this->force = false;
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
