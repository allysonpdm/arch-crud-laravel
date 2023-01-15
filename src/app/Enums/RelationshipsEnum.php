<?php

namespace ArchCrudLaravel\App\Enums;

use ArchCrudLaravel\App\Enums\Traits\GetValues;

enum RelationshipsEnum: string
{
    use GetValues;

    case HAS_ONE = 'HasOne';
    case HAS_MANY = 'HasMany';
    case BELONGS_TO = 'BelongsTo';
    case BELONGS_TO_MANY = 'BelongsToMany';
    case MORPH_TO = 'MorphTo';
    case MORPH_TO_MANY = 'MorphToMany';
}