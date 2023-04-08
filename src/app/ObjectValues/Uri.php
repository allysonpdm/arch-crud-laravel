<?php

namespace ArchCrudLaravel\App\ObjectValues;

use ArchCrudLaravel\App\ObjectValues\Contracts\ObjectValue;
use ArchCrudLaravel\App\Rules\UriRule;
use InvalidArgumentException;
use Stringable;

class Uri extends ObjectValue implements Stringable
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
