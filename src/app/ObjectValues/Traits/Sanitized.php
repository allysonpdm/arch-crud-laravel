<?php

namespace ArchCrudLaravel\App\ObjectValues\Traits;

use ArchCrudLaravel\App\ObjectValues\Regex;

trait Sanitized
{
    protected string $regex;

    public function sanitized(): string
    {
        return preg_replace($this->regex, '', $this->value);
    }

    public function setRegex(Regex|string $regex): void
    {
        if (is_string($regex)) {
            $regex = new Regex($regex);
        }
        $this->regex = $regex;
    }

}
