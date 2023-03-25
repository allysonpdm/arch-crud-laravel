<?php

namespace ArchCrudLaravel\App\Services\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use ReflectionClass;

trait Relationships
{

    protected static function hasRelationships(Model $register): bool
    {
        $relationshipNames = self::getRelationshipNames(model: $register);

        foreach ($relationshipNames as $relationshipName) {
            if ($register->{$relationshipName}->exists()) {
                return true;
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
