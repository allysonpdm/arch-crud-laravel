<?php

namespace ArchCrudLaravel\App\Http\Requests;

use ArchCrudLaravel\App\Http\Requests\Traits\{
    IndexRules,
    UpdateRules,
    DeleteRules
};
use Illuminate\Foundation\Http\FormRequest;

abstract class BaseRequest extends FormRequest
{
    use IndexRules, UpdateRules, DeleteRules;
    
    abstract public function authorize(): bool;
    abstract public function rules(): array;

    abstract protected function hasGroupPermission(): bool;
    abstract protected function isOwner(string $method): bool;
}