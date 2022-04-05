<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

abstract class BaseRequest extends FormRequest
{
    const CONDITIONS_OPERATORS = ['like', '=', '!=', '<>', '<', '>', '<=', '>='];
    protected $model;

    abstract public function authorize(): bool;
    abstract public function rules(): array;

    protected function indexRequest(): array
    {
        return [
            'page'=> 'integer',
            'perPage'=> 'integer',
            'orderBy'=> 'array',
            'orderBy.*' => [
                Rule::in(['asc', 'desc'])
            ],

            'wheres' => 'array',
            'wheres.*.column' => [
                'string',
                'required',
                Rule::in($this->model::$searchable),
            ],
            'wheres.*.condition' => [
                'string',
                'required',
                Rule::in(self::CONDITIONS_OPERATORS)
            ],
            'wheres.*.search' => 'required|string',

            'orWheres' => 'array',
            'orWheres.*.column' => [
                'string',
                'required',
                Rule::in($this->model::$searchable)
            ],
            'orWheres.*.condition' => [
                'string',
                'required',
                Rule::in(self::CONDITIONS_OPERATORS)
            ],
            'orWheres.*.search' => 'string|required',
        ];
    }
}
