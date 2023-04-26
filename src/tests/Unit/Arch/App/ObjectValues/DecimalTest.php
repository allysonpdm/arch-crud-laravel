<?php

namespace ArchCrudLaravel\Tests\Unit\Arch\App\ObjectValues;

use ArchCrudLaravel\App\ObjectValues\Decimal;
use InvalidArgumentException;
use Tests\TestCase;

class DecimalTest extends TestCase
{
    public function validProvider()
    {
        return [
            [1234.56, 2, '1234.56'],
            [0.00, 2, '0.00'],
            [1000, 0, '1000'],
            [123456789.123456789, 9, '123456789.123456789'],
            [999.99, 3, '999.990'],
            [123.45, 4, '123.4500'],
        ];
    }

    /**
     * @dataProvider validProvider
     */
    public function testValid($value, $precision, $expected)
    {
        $decimal = new Decimal($value, $precision);
        $this->assertEquals($expected, (string) $decimal);
    }

    public function invalidProvider()
    {
        return [
            ['invalid_value', 2],
            [null, 2],
            [true, 2],
            [[1,2,3], 2],
            [123.45, 'invalid_precision'],
            [123.45, -2],
            [123.45, 20],
        ];
    }

    /**
     * @dataProvider invalidProvider
     */
    public function testInvalid($value, $precision)
    {
        $this->expectException(InvalidArgumentException::class);
        new Decimal($value, $precision);
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
