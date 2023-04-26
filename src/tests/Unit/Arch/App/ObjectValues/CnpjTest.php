<?php

namespace ArchCrudLaravel\Tests\Unit\Arch\App\ObjectValues;

use ArchCrudLaravel\App\ObjectValues\Cnpj;
use InvalidArgumentException;
use Tests\TestCase;

class CnpjTest extends TestCase
{
    /**
     * @dataProvider validProvider
     */
    public function testValid($cnpjNumber, $expected)
    {
        $cnpj = new Cnpj($cnpjNumber);
        $this->assertEquals($expected, (string) $cnpj);
    }

    public function validProvider()
    {
        return [
            ['70.907.631/0001-74', '70907631000174'],
            ['34.913.822/0001-85', '34913822000185'],
            ['28947249000128', '28947249000128'],
            ['77172147000193', '77172147000193'],
            ['39567339000100', '39567339000100'],
        ];
    }

    /**
     * @dataProvider invalidProvider
     */
    public function testInvalid($cnpjNumber)
    {
        $this->expectException(InvalidArgumentException::class);
        new Cnpj($cnpjNumber);
    }

    public function invalidProvider()
    {
        return [
            ['00.000.000/0000-00'],
            ['00000000000000'],
            ['11.111.111/1111-11'],
            ['11111111111111'],
            ['22.222.222/2222-22'],
            ['22222222222222'],
            ['33.333.333/3333-33'],
            ['33333333333333'],
            ['44.444.444/4444-44'],
            ['44444444444444'],
            ['55.555.555/5555-55'],
            ['55555555555555'],
            ['66.666.666/6666-66'],
            ['66666666666666'],
            ['77.777.777/7777-77'],
            ['77777777777777'],
            ['88.888.888/8888-88'],
            ['99.999.999/9999-00'],
            ['abc.def.ghi-jk'],
        ];
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
