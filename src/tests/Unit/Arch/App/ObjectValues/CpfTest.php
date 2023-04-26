<?php

namespace ArchCrudLaravel\Tests\Unit\Arch\App\ObjectValues;

use ArchCrudLaravel\App\ObjectValues\Cpf;
use InvalidArgumentException;
use Tests\TestCase;

class CpfTest extends TestCase
{
    /**
     * @dataProvider validProvider
     */
    public function testValid($cpf, $expected)
    {
        $cpf = new Cpf($cpf);
        $this->assertEquals($expected, $cpf);
    }

    public function validProvider()
    {
        return [
            ['794.268.380-07', '79426838007'],
            ['109.870.650-17', '10987065017'],
            ['17678938098', '17678938098'],
            ['47696753053', '47696753053'],
            ['907.745.290-75', '90774529075'],
        ];
    }

    /**
     * @dataProvider invalidProvider
     */
    public function testInvalid($cpf)
    {
        $this->expectException(InvalidArgumentException::class);
        new Cpf($cpf);
    }

    public function invalidProvider()
    {
        return [
            ['00000000000'],
            ['000.000.000-00'],
            ['11111111111'],
            ['111.111.111-11'],
            ['22222222222'],
            ['222.222.222-22'],
            ['33333333333'],
            ['333.333.333-33'],
            ['444.444.444-44'],
            ['555.555.555-55'],
            ['666.666.666-66'],
            ['777.777.777-77'],
            ['888.888.888-88'],
            ['999.999.999-00'],
            ['abc.def.ghi-jk'],
        ];
    }

    public function testSanitization()
    {
        $cpf = new Cpf('529.982.247-25');

        $this->assertEquals('52998224725', $cpf->sanitized());
    }

    public function testMasked()
    {
        $cpf = new Cpf('52998224725');

        $this->assertEquals('529.982.247-25', $cpf->masked());
    }
}
