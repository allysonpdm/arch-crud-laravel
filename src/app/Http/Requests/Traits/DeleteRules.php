<?php

namespace ArchCrudLaravel\App\Http\Requests\Traits;

trait DeleteRules
{
    public const CONDITIONS_OPERATORS = ['=', '!=', '<>', '<', '>', '<=', '>=', 'LIKE', 'NOT LIKE', 'IS NULL', 'IS NOT NULL'];

    protected function deleteRules(): array
    {
        return [
            'force' => 'bail|boolean',
        ];
    }
}