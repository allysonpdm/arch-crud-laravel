<?php

namespace ArchCrudLaravel\App\Http\Requests\Traits;

trait DestroyRules
{
    protected function destroyRules(): array
    {
        return [
            'force' => 'bail|boolean',
        ];
    }
}
