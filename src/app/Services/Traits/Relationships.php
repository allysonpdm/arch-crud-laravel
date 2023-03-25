<?php

namespace ArchCrudLaravel\App\Services\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use ReflectionClass;

trait Relationships
{

    protected static function hasRelationships(Model $register): bool
    {
        $has = false;
        $relations = self::getRelationships($register);

        foreach ($relations as $relation) {
            if (!empty($register->{$relation}) && $register->{$relation}->count() > 0) {
                $has = true;
            }
        }
        return $has;
    }

    protected static function getRelationships(
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
