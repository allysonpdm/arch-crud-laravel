<?php

namespace ArchCrudLaravel\Tests\Unit\Arch\App\ObjectValues;

use ArchCrudLaravel\App\ObjectValues\Monetario;
use InvalidArgumentException;
use Tests\TestCase;

class MonetarioTest extends TestCase
{

    /**
     * @dataProvider validProvider
     */
    public function testValid($value, $expectedOutput)
    {
        $monetario = new Monetario($value);

        $this->assertEquals($expectedOutput, (string) $monetario);
    }

    public function validProvider()
    {
        return [
            [1234.56, '1.234,56'],
            [0, '0,00'],
            [999999999.99, '999.999.999,99'],
            [123.45, '123,45'],
        ];
    }

    /**
     * @dataProvider invalidProvider
     */
    public function testInvalid($value)
    {
        $this->expectException(InvalidArgumentException::class);

        new Monetario($value);
    }

    public function invalidProvider()
    {
        return [
            ['abc'],
            ['1,234.56'],
            ['1.234,56'],
            [null],
            [false],
        ];
    }

    public function testWithSymbol()
    {
        $monetario = new Monetario(1234.56, 2, 'R$');

        $this->assertEquals('R$ 1.234,56', (string) $monetario);
    }

    public function testWithSeparators()
    {
        $monetario = new Monetario(1234.56);
        $monetario->setSeparators(',', '.');

        $this->assertEquals('1,234.56', (string) $monetario);
    }

    public function testSanitization()
    {
        $monetario = new Monetario(1234.56);

        $this->assertEquals('1234.56', $monetario->sanitized());
    }

}
