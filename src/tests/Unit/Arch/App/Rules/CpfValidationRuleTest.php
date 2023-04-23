<?php

namespace ArchCrudLaravel\Tests\Unit\Arch\App\Rules;

use ArchCrudLaravel\App\Rules\CpfValidationRule;
use Tests\TestCase;

class CpfValidationRuleTest extends TestCase
{
    /**
     * @dataProvider validCpfDataProvider
     */
    public function testValidCpf($cpf)
    {
        $rule = new CpfValidationRule();
        $this->assertTrue($rule->passes('cpf', $cpf));
    }

    /**
     * @dataProvider invalidCpfDataProvider
     */
    public function testInvalidCpf($cpf)
    {
        $rule = new CpfValidationRule();
        $this->assertFalse($rule->passes('cpf', $cpf));
    }

    public function validCpfDataProvider()
    {
        return [
            ['529.982.247-25'],
            ['054.465.400-50'],
            ['39053344705'],
        ];
    }

    public function invalidCpfDataProvider()
    {
        return [
            ['111.111.111-11'],
            ['123.456.789-10'],
            ['12345678900'],
            [''],
            [null],
        ];
    }
}
