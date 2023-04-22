<?php

namespace ArchCrudLaravel\Tests\Unit\Arch\App\ObjectValues;

use ArchCrudLaravel\App\ObjectValues\Cnpj;
use InvalidArgumentException;
use Tests\TestCase;

class CnpjTest extends TestCase
{
    public function testValidCnpj()
    {
        $cnpj = new Cnpj('45.987.101/0001-98');

        $this->assertEquals('45987101000198', (string) $cnpj);
    }

    public function testInvalidCnpj()
    {
        $this->expectException(InvalidArgumentException::class);

        new Cnpj('11.111.111/1111-11');
    }

    public function testCnpjSanitization()
    {
        $cnpj = new Cnpj('45.987.101/0001-98');

        $this->assertEquals('45987101000198', $cnpj->sanitized());
    }

    public function testCnpjMasked()
    {
        $cnpj = new Cnpj('45987101000198');

        $this->assertEquals('45.987.101/0001-98', $cnpj->masked());
    }
}
