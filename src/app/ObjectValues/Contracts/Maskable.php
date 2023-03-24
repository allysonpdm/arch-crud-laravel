<?php

namespace ArchCrudLaravel\App\ObjectValues\Contracts;

interface Maskable {
    /**
     * Returns the value formatted according to the object's mask.
     *
     * @return string
     */
    public function masked(): string;

    /**
     * Returns the object's mask.
     *
     * @return string
     */
    public function setMask(string $mask): void;
}
