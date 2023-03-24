<?php

namespace App\ObjectValues;

use App\ObjectValues\Contracts\ObjectValue;
use App\Rules\UriRule;
use InvalidArgumentException;

class Uri extends ObjectValue
{
    public function __construct(protected mixed $value)
    {
        parent::__construct($value);
    }

    protected function validate(mixed $value): void
    {
        $rule = new UriRule();

        if (!$rule->passes('', $value)) {
            throw new InvalidArgumentException('O valor informado não é uma URL válida.');
        }
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
