<?php

namespace ArchCrudLaravel\Tests\Unit\Arch\App\ObjectValues;

use ArchCrudLaravel\App\ObjectValues\Cpf;
use InvalidArgumentException;
use Tests\TestCase;

class CpfTest extends TestCase
{
    public function testValidCpf()
    {
        $cpf = new Cpf('529.982.247-25');

        $this->assertEquals('52998224725', (string) $cpf);
    }

    public function testInvalidCpf()
    {
        $this->expectException(InvalidArgumentException::class);

        new Cpf('111.111.111-11');
    }

    public function testCpfSanitization()
    {
        $cpf = new Cpf('529.982.247-25');

        $this->assertEquals('52998224725', $cpf->sanitized());
    }

    public function testCpfMasked()
    {
        $cpf = new Cpf('52998224725');

        $this->assertEquals('529.982.247-25', $cpf->masked());
    }
}
