<?php

namespace ArchCrudLaravel\Tests\Unit\Arch\App\ObjectValues;

use ArchCrudLaravel\App\ObjectValues\Cpf;
use InvalidArgumentException;
use Tests\TestCase;

class CpfTest extends TestCase
{
    public function testValid()
    {
        $cpf = new Cpf('529.982.247-25');

        $this->assertEquals('52998224725', (string) $cpf);
    }

    public function testInvalid()
    {
        $this->expectException(InvalidArgumentException::class);

        new Cpf('111.111.111-11');
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
