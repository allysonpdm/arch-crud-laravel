<?php

namespace ArchCrudLaravel\App\ObjectValues;

use ArchCrudLaravel\App\ObjectValues\Contracts\ObjectValue;
use ArchCrudLaravel\App\Rules\DecimalRule;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;
use Stringable;

class Decimal extends ObjectValue implements Stringable
{
    protected ?string $decimalSeparator = '.';
    protected ?string $thousandsSeparator = '';

    public function __construct(
        protected mixed $value,
        protected ?int $decimals = 2
    )
    {
        $value = ($value === null) ? 0 : $value;
        parent::__construct($value);

        if (!in_array($this->decimalSeparator, ['.', ','])) {
            throw new InvalidArgumentException('Invalid decimal separator');
        }
    }

    public function setDecimalSeparator(string $separator): void
    {
        if (!in_array($separator, ['.', ','])) {
            throw new InvalidArgumentException('Invalid decimal separator');
        }

        $this->decimalSeparator = $separator;
    }

    public function setThousandsSeparator(string $separator): void
    {
        $this->thousandsSeparator = $separator;
    }

    protected function validate(mixed $value): void
    {
        $validator = Validator::make(
            ['decimal' => $value],
            [
                'decimal' => [
                    'required',
                    'numeric',
                    new DecimalRule($this->decimals),
                ],
            ]
        );

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }
    }

    public function __toString()
    {
        return number_format(
            $this->value,
            $this->decimals,
            $this->decimalSeparator,
            $this->thousandsSeparator
        );
    }
}
