<?php

namespace ArchCrudLaravel\App\Services\Traits;

use Illuminate\Database\Eloquent\Model;
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
    MorphToMany,
    Relation
};
use ReflectionClass;

trait Relationships
{

    protected static function hasRelationships(Model $register): bool
    {
        $relationshipNames = self::getRelationshipNames(model: $register);

        foreach ($relationshipNames as $relationshipName) {
            $relation = $register->{$relationshipName}();

            switch (get_class($relation)) {
                case HasOne::class:
                case BelongsTo::class:
                case MorphTo::class:
                case MorphOne::class:
                case MorphToMany::class:
                case MorphedByMany::class:
                case HasOneThrough::class:
                    if ($register->{$relationshipName}->exists()) {
                        return true;
                    }
                    break;
                case HasMany::class:
                case BelongsToMany::class:
                case MorphMany::class:
                case HasManyThrough::class:
                case HasOneOrManyThrough::class:
                    if ($register->{$relationshipName}->isNotEmpty()) {
                        return true;
                    }
                    break;
                default:
                    break;
            }
        }

        return false;
    }

    protected static function getRelationshipNames(
        Model $model,
        array $ignoreTypes = [],
        array $ignoreRelationships = []
    ): array
    {
        $reflector = new ReflectionClass($model);
        $relations = [];
        foreach ($reflector->getMethods() as $reflectionMethod) {
            $returnType = $reflectionMethod->getReturnType();
            if ($returnType && is_subclass_of($returnType->getName(), Relation::class)) {
                $relationName = $reflectionMethod->name;
                $relationType = class_basename($returnType->getName());
                if (!in_array($relationType, $ignoreTypes) && !in_array($relationName, $ignoreRelationships)) {
                    $relations[] = $relationName;
                }
            }
        }
        return $relations;
    }
}
