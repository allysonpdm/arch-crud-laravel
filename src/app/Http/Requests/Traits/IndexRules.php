<?php

namespace ArchCrudLaravel\App\Http\Requests\Traits;

use ArchCrudLaravel\App\Models\BaseModel;
use ArchCrudLaravel\App\Rules\FieldsExistsInTableRule;
use Illuminate\Validation\Rule;

trait IndexRules
{
    public array $conditionsOperators = ['=', '!=', '<>', '<', '>', '<=', '>=', 'LIKE', 'NOT LIKE', 'IS NULL', 'IS NOT NULL'];

    protected $model;

    protected function indexRules(): array
    {
        $table = app($this->model)->getTable();
        $connection = app($this->model)->getConnectionName();
        return [
            'page' => 'integer',
            'perPage' => 'integer',
            'orderBy' => [
                'array',
                new FieldsExistsInTableRule($table, $connection)
            ],
            'orderBy.*' => [
                Rule::in(['asc', 'desc']),
            ],
            'wheres' => 'array',
            'wheres.*.column' => [
                'string',
                'required',
                Rule::in($this->model->searchable),
                new FieldsExistsInTableRule($table, $connection)
            ],
            'wheres.*.condition' => [
                'string',
                'required',
                Rule::in($this->conditionsOperators),
            ],
            'wheres.*.search' => 'required|string',

            'orWheres' => 'array',
            'orWheres.*.column' => [
                'string',
                'required',
                Rule::in($this->model->searchable),
                new FieldsExistsInTableRule($table, $connection)
            ],
            'orWheres.*.condition' => [
                'string',
                'required',
                Rule::in($this->conditionsOperators)
            ],
            'orWheres.*.search' => 'string|required',
        ];
    }
}
