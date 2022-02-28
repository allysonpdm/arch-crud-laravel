<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

abstract class BaseRequest extends FormRequest
{
    const CONDITIONS_OPERATORS = ['like', '=', '!=', '<>', '<', '>', '<=', '>='];

    abstract public function authorize(): bool;
    abstract public function rules(): array;
}
