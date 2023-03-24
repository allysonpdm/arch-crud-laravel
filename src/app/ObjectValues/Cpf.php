<?php

namespace App\ObjectValues;

use App\Rules\CpfValidationRule;
use App\ObjectValues\Contracts\{
    Maskable,
    ObjectValue,
    Sanitizable
};
use App\ObjectValues\Traits\Masked;
use App\ObjectValues\Traits\Sanitized;
use InvalidArgumentException;

class Cpf extends ObjectValue implements Maskable, Sanitizable
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
            throw new InvalidArgumentException('CPF inválido.');
        }
    }

    public function __toString()
    {
        return str_pad($this->value, 11, '0', STR_PAD_LEFT);
    }

}
