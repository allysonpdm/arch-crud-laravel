<?php

namespace ArchCrudLaravel\Tests\Unit\Arch\App\ObjectValues;

use ArchCrudLaravel\App\ObjectValues\Decimal;
use InvalidArgumentException;
use Tests\TestCase;

class DecimalTest extends TestCase
{
    public function testValid()
    {
        $decimal = new Decimal(1234.56, 2);

        $this->assertEquals('1234.56', (string) $decimal);
    }

    public function testInvalid()
    {
        $this->expectException(InvalidArgumentException::class);

        new Decimal('invalid_value', 2);
    }

    public function testInvalidSeparator()
    {
        $this->expectException(InvalidArgumentException::class);

        $decimal = new Decimal(1234.5678, 2);
        $decimal->setDecimalSeparator('/');
    }

    public function testValidWithCustomSeparators()
    {
        $decimal = new Decimal(1234567.891, 3);
        $decimal->setDecimalSeparator(',');
        $decimal->setThousandsSeparator('.');

        $this->assertEquals('1.234.567,891', (string) $decimal);
    }
}
