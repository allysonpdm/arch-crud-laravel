<?php

namespace ArchCrudLaravel\App\ObjectValues;

use ArchCrudLaravel\App\Rules\CpfValidationRule;
use ArchCrudLaravel\App\ObjectValues\Contracts\{
    Maskable,
    ObjectValue,
    Sanitizable
};
use ArchCrudLaravel\App\ObjectValues\Traits\{
    Masked,
    Sanitized
};
use InvalidArgumentException;
use Stringable;

class Cpf extends ObjectValue implements Maskable, Sanitizable, Stringable
{
    use Masked, Sanitized;

    public function __construct(protected mixed $value)
    {
        parent::__construct($value);
        $this->setMask('###.###.###-##');
        $this->setRegex('[^0-9]');
        $this->value = $this->sanitized();
    }

    protected function validate(mixed $value): void
    {
        $rule = new CpfValidationRule;

        if (!$rule->passes('', $value)) {
            throw new InvalidArgumentException("O CPF: '$value' é inválido.");
        }
    }

    public function __toString()
    {
        return str_pad($this->value, 11, '0', STR_PAD_LEFT);
    }

}
