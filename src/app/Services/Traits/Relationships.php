<?php

namespace ArchCrudLaravel\App\Services\Traits;

use ArchCrudLaravel\App\Exceptions\SoftDeleteException;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Response;
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

    protected static function getRelationships(Model $model): array
    {
        $typesOfRelationships = [
            'HasOne',
            'HasMany',
            'BelongsTo',
            'BelongsToMany',
            'MorphTo',
            'MorphToMany'
        ];
        $reflector = new ReflectionClass($model);
        $relations = [];
        foreach ($reflector->getMethods() as $reflectionMethod) {
            $returnType = $reflectionMethod->getReturnType();
            if ($returnType && (in_array(class_basename($returnType->getName()), $typesOfRelationships))) {
                $relations[] = $reflectionMethod->name;
            }
        }

        return $relations;
    }
}
