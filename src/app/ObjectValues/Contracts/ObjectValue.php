<?php

namespace ArchCrudLaravel\App\ObjectValues\Contracts;

use Stringable;

abstract class ObjectValue implements Stringable
{
    public function __construct(protected mixed $value)
    {
        $this->validate($value);
        $this->value = $value;
    }

    protected abstract function validate(mixed $value): void;
    public abstract function __toString();

    public function getValue(): mixed
    {
        return $this->value;
    }
}
