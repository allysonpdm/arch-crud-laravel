<?php

namespace ArchCrudLaravel\App\ObjectValues;

class Monetario extends Decimal
{
    protected ?string $symbol = null;
    protected ?string $thousandsSeparator = '.';
    protected ?string $decimalSeparator = ',';

    public function __construct(
        protected mixed $value,
        ?int $decimals = 2
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
