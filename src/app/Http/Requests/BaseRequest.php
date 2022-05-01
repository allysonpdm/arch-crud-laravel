<?php

namespace App\Http\Requests;

use App\Rules\FieldsExistsInTableRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

abstract class BaseRequest extends FormRequest
{
    const DESENVOLVEDOR = 'Desenvolvedor';
    const ADMINISTRADOR = 'Administrador';
    const GERENTE = 'Gerente';
    const DEPARTAMENTO_ADMINISTRATIVO = 'Administrativo';
    const DEPARTAMENTO_JURIDICO = 'Jurídico';
    const COLABORADOR = 'Colaborador';
    const PROPRIETARIO = 'Proprietário';

    public const CONDITIONS_OPERATORS = ['like', '=', '!=', '<>', '<', '>', '<=', '>='];
    protected $autorizados = [];
    protected $model;

    abstract public function authorize(): bool;
    abstract public function rules(): array;

    abstract protected function hasPermission(): bool;

    protected function indexRequest(): array
    {
        return [
            'page'=> 'integer',
            'perPage'=> 'integer',
            'orderBy'=> [
                'array',
                new FieldsExistsInTableRule(app($this->model)->getTable())
            ],
            'orderBy.*' => [
                Rule::in(['asc', 'desc']),
            ],
            'wheres' => 'array',
            'wheres.*.column' => [
                'string',
                'required',
                Rule::in($this->model::$searchable),
                new FieldsExistsInTableRule(app($this->model)->getTable())
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
                new FieldsExistsInTableRule(app($this->model)->getTable())
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
