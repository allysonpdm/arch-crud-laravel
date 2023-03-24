<?php

namespace App\ObjectValues\Contracts;

use App\ObjectValues\Regex;

interface Sanitizable {
    /**
     * Returns the value formatted according to the object's mask.
     *
     * @return string
     */
    public function sanitized(): string;

    /**
     * Returns the object's mask.
     *
     * @return string
     */
    public function setRegex(Regex|string $regex): void;
}
