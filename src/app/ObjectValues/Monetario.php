<?php

namespace ArchCrudLaravel\App\ObjectValues;

use Stringable;

class Monetario extends Decimal implements Stringable
{
    protected ?string $thousandsSeparator = '.';
    protected ?string $decimalSeparator = ',';

    public function __construct(
        protected mixed $value,
        ?int $decimals = 2,
        protected ?string $symbol = null
    )
    {
        $value = ($value === null) ? 0 : $value;
        parent::__construct($value, $decimals);
    }

    public function setSymbol(string $symbol): void
    {
        $this->symbol = $symbol;
    }

    public function setSeparators(string $thousandsSeparator, string $decimalSeparator): void
    {
        $this->thousandsSeparator = $thousandsSeparator;
        $this->decimalSeparator = $decimalSeparator;
    }

    public function __toString()
    {
        $formatted = number_format($this->value, $this->decimals, $this->decimalSeparator, $this->thousandsSeparator);

        if (!empty($this->symbol)) {
            $formatted = trim($this->symbol . ' ' . $formatted);
        }

        return $formatted;
    }
}
