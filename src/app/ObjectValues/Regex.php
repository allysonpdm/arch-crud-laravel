<?php

namespace App\ObjectValues;

use App\ObjectValues\Contracts\ObjectValue;
use InvalidArgumentException;

class Regex extends ObjectValue
{
    public function __construct(
        protected mixed $value,
        protected bool $global = false,
        protected bool $multiLine = false,
        protected bool $insensitive = false,
        protected bool $extended = false,
        protected bool $singleLine = false,
        protected bool $unicode = false,
        protected bool $ungreedy = false,
        protected bool $anchored = false,
        protected bool $jChanged = false,
        protected bool $dollarEndOnly = false
    )
    {
        parent::__construct($value);
    }

    protected function validate(mixed $value): void
    {
        if (!is_string($value)) {
            throw new InvalidArgumentException('The value must be a string.');
        }
    }

    public function __toString()
    {
        return '/' . $this->value . '/'
            . ($this->multiLine ? 'm' : '')
            . ($this->insensitive ? 'i' : '')
            . ($this->extended ? 'x' : '')
            . ($this->singleLine ? 's' : '')
            . ($this->unicode ? 'u' : '')
            . ($this->ungreedy ? 'U' : '')
            . ($this->anchored ? 'A' : '')
            . ($this->jChanged ? 'J' : '')
            . ($this->dollarEndOnly ? 'D' : '')
            . ($this->global ? 'g' : '');
    }
}
