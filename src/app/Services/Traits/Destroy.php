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
        if (!self::isActive($this->register, $this->model::DELETED_AT, $this->force)) {
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
    ) {
        $relations = self::getRelationshipNames(
            model: $register,
            ignoreTypes: $ignoreTypesOfRelationships,
            ignoreRelationships: $ignoreRelationships
        );
        foreach ($relations as $relationName) {
            $relation = $register->{$relationName}();
            self::removeRelations($relation, $register->{$relationName});
        }
        return $register->delete();
    }

    protected function softDelete(Model $register, string $nameColumn, string $value): bool
    {
        if ($this->isModelVisited($register)) {
            return true;
        }

        $this->markModelVisited($register);

        if ($register->exists) {
            $this->processRelatedItemsForSoftDelete($register, $nameColumn, $value);
            return $register->update([$nameColumn => $value]);
        }

        return false;
    }

    protected static function removeRelations($relation, $register)
    {
        if ($register === null) {
            return;
        }

        if (self::isSupportedRelation($relation)) {
            foreach ($register as $related) {
                if ($related instanceof Model) {
                    self::hardDelete($related);
                }
            }
        }
    }

    protected function processRelatedItemsForSoftDelete(Model $register, string $nameColumn, string $value): void
    {
        $relations = self::getRelationshipNames(model: $register);

        foreach ($relations as $relationName) {
            $relation = $register->{$relationName}();

            if (self::isSupportedRelation($relation)) {
                $relatedItems = $register->{$relationName};
                $this->processSoftDeleteOnRelatedItems($relatedItems, $nameColumn, $value);
            }
        }
    }

    protected function processSoftDeleteOnRelatedItems($relatedItems, string $nameColumn, string $value): void
    {
        if ($relatedItems instanceof Collection || is_array($relatedItems)) {
            foreach ($relatedItems as $related) {
                if ($related !== null && $related instanceof Model && $related->exists) {
                    $this->softDelete(register: $related, nameColumn: $nameColumn, value: $value);
                }
            }
        } elseif ($relatedItems instanceof Model) {
            if ($relatedItems !== null && $relatedItems->exists) {
                $this->softDelete(register: $relatedItems, nameColumn: $nameColumn, value: $value);
            }
        }
    }

    protected static function isActive(Model $register, string $nameColumn, bool $force): bool
    {
        return empty($register->{$nameColumn}) || $force;
    }

    protected function afterDelete()
    {
        return $this;
    }
}
