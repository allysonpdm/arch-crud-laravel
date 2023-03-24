<?php

namespace App\ObjectValues;

use App\ObjectValues\Contracts\{
    Maskable,
    ObjectValue,
    Sanitizable
};
use App\ObjectValues\Traits\{
    Masked,
    Sanitized
};
use App\Rules\CnpjValidationRule;
use App\Rules\CpfValidationRule;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;

class CpfCnpj extends ObjectValue implements Maskable, Sanitizable
{
    use Masked, Sanitized;

    protected bool $isCpf;

    public function __construct(protected mixed $value)
    {
        $this->value = $value;
        $this->setRegex(new Regex('[^0-9]'));
        $this->value = $this->sanitized();
        if ($this->isCpf()) {
            $this->setMask('###.###.###-##');
        } else {
            $this->setMask('##.###.###/####-##');
        }
        $this->validate($this->value);
    }

    protected function validate(mixed $value): void
    {
        $rule = $this->isCpf() ? new CpfValidationRule() : new CnpjValidationRule();

        $validator = Validator::make(
            ['cpf_cnpj' => $value],
            [
                'cpf_cnpj' => [
                    'required',
                    $rule,
                ]
            ]
        );

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            throw new InvalidArgumentException(collect($errors)->first());
        }
    }

    public function isCpf(): bool
    {
        $this->isCpf = strlen($this->value) === 11;
        return $this->isCpf;
    }

    public function isCnpj(): bool
    {
        return !$this->isCpf();
    }

    public function __toString()
    {
        return $this->isCpf() ? new Cpf($this->value) : new Cnpj($this->value);
    }
}
