<?php

namespace ArchCrudLaravel\App\ObjectValues\Traits;

trait Masked
{
    protected string $mask;
    protected mixed $value;

    public function masked(): string
    {
        $formatted = '';

        $digits = str_split($this->value);
        $maskChars = str_split($this->mask);

        foreach ($maskChars as $char) {
            $formatted .= ($char == '#')
                ? array_shift($digits)
                : $char;
        }

        return $formatted;
    }

    public function setMask(string $mask): void
    {
        $this->mask = $mask;
    }
}
