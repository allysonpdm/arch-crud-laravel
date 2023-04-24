<?php

namespace ArchCrudLaravel\Tests\Unit\Arch\App\Rules;

use ArchCrudLaravel\App\Rules\CnpjValidationRule;
use Tests\TestCase;

class CnpjValidationRuleTest extends TestCase
{
    /**
     * @dataProvider validDataProvider
     */
    public function testValid($cnpj)
    {
        $rule = new CnpjValidationRule();
        $this->assertTrue($rule->passes('cnpj', $cnpj));
    }

    public function validDataProvider()
    {
        return [
            ['60.872.504/0001-23'],
            ['63.883.437/0001-30'],
            ['60780238000109'],
        ];
    }

    /**
     * @dataProvider invalidDataProvider
     */
    public function testInvalid($cnpj)
    {
        $rule = new CnpjValidationRule();
        $this->assertFalse($rule->passes('cnpj', $cnpj));
    }

    public function invalidDataProvider()
    {
        return [
            ['11.111.111/1111-11'],
            ['00.000.000/0000-00'],
            ['12345678901234'],
            [''],
            [null],
        ];
    }
}