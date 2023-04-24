<?php

namespace ArchCrudLaravel\Tests\Unit\Arch\App\Rules;

use ArchCrudLaravel\App\Rules\CpfValidationRule;
use Tests\TestCase;

class CpfValidationRuleTest extends TestCase
{
    /**
     * @dataProvider validDataProvider
     */
    public function testValid($cpf)
    {
        $rule = new CpfValidationRule();
        $this->assertTrue($rule->passes('cpf', $cpf));
    }

    public function validDataProvider()
    {
        return [
            ['529.982.247-25'],
            ['054.465.400-50'],
            ['39053344705'],
        ];
    }

    /**
     * @dataProvider invalidDataProvider
     */
    public function testInvalid($cpf)
    {
        $rule = new CpfValidationRule();
        $this->assertFalse($rule->passes('cpf', $cpf));
    }

    public function invalidDataProvider()
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
