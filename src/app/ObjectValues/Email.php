<?php

namespace ArchCrudLaravel\App\ObjectValues;

use ArchCrudLaravel\App\Rules\EmailMxValidationRule;
use ArchCrudLaravel\App\ObjectValues\Contracts\ObjectValue;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use InvalidArgumentException;

class Email extends ObjectValue
{
    protected function validate(mixed $value): void
    {
        $validator = Validator::make(
            data:['email' => $value],
            rules: [
                'email' => [
                    'required',
                    'email',
                    new EmailMxValidationRule()
                ],
            ]
        );

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }
    }

    public function __toString()
    {
        return Str::lower($this->value);
    }
}
