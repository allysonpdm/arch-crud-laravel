<?php

namespace ArchCrudLaravel\Tests\Unit\Arch\App\ObjectValues;

use ArchCrudLaravel\App\ObjectValues\Cnpj;
use InvalidArgumentException;
use Tests\TestCase;

class CnpjTest extends TestCase
{
    public function testValid()
    {
        $cnpj = new Cnpj('32.631.433/0001-31');

        $this->assertEquals('32631433000131', (string) $cnpj);
    }

    public function testInvalid()
    {
        $this->expectException(InvalidArgumentException::class);

        new Cnpj('11.111.111/1111-11');
    }

    public function testSanitization()
    {
        $cnpj = new Cnpj('32.631.433/0001-31');

        $this->assertEquals('32631433000131', $cnpj->sanitized());
    }

    public function testMasked()
    {
        $cnpj = new Cnpj('32631433000131');

        $this->assertEquals('32.631.433/0001-31', $cnpj->masked());
    }
}
