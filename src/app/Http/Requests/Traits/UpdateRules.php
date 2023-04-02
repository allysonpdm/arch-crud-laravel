<?php

namespace ArchCrudLaravel\App\Http\Requests\Traits;

use Illuminate\Database\Eloquent\Model;

trait UpdateRules
{
    protected Model $model;

    protected function updateRules(): array
    {
        return [
            $this->model::DELETED_AT => 'bail|nullable|date_format:"Y-m-d H:i:s"|after_or_equal:today',
        ];
    }
}
