<?php

namespace ArchCrudLaravel\App\Enums\Traits;

trait GetValues
{
    public static function allValues(): array
    {
        return array_column(self::cases(), 'value');
    }
}
