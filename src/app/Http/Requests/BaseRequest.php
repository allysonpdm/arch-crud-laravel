<?php

namespace ArchCrudLaravel\App\Http\Requests;

use ArchCrudLaravel\App\Rules\FieldsExistsInTableRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

abstract class BaseRequest extends FormRequest
{
    public const CONDITIONS_OPERATORS = ['like', '=', '!=', '<>', '<', '>', '<=', '>='];
    protected $model;

    abstract public function authorize(): bool;
    abstract public function rules(): array;

    abstract protected function hasGroupPermission(): bool;
    abstract protected function isOwner(string $method): bool;

    protected function indexRequest(): array
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
                Rule::in($this->model::$searchable),
                new FieldsExistsInTableRule($table, $connection)
            ],
            'wheres.*.condition' => [
                'string',
                'required',
                Rule::in(self::CONDITIONS_OPERATORS),
            ],
            'wheres.*.search' => 'required|string',

            'orWheres' => 'array',
            'orWheres.*.column' => [
                'string',
                'required',
                Rule::in($this->model::$searchable),
                new FieldsExistsInTableRule($table, $connection)
            ],
            'orWheres.*.condition' => [
                'string',
                'required',
                Rule::in(self::CONDITIONS_OPERATORS)
            ],
            'orWheres.*.search' => 'string|required',
        ];
    }

    protected function destroyRequest(): array
    {
        return [
            'force' => 'bail|boolean',
        ];
    }
}