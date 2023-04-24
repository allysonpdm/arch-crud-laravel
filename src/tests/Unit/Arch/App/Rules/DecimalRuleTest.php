<?php

namespace ArchCrudLaravel\Tests\Unit\Arch\App\Rules;

use ArchCrudLaravel\App\Rules\DecimalRule;
use InvalidArgumentException;
use Tests\TestCase;

class DecimalRuleTest extends TestCase
{
    /**
     * @dataProvider validDataProvider
     */
    public function testValid($decimalPlaces, $value)
    {
        $rule = new DecimalRule($decimalPlaces);
        $this->assertTrue($rule->passes('decimal', $value));
    }

    public function validDataProvider()
    {
        return [
            [2, '123.45'],
            [2, '-123.45'],
            [2, '123'],
            [3, '123.456'],
            [0, '123'],
        ];
    }

    /**
     * @dataProvider invalidDataProvider
     */
    public function testInvalid($decimalPlaces, $value)
    {
        $rule = new DecimalRule($decimalPlaces);
        $this->assertFalse($rule->passes('decimal', $value));
    }

    public function invalidDataProvider()
    {
        return [
            [2, '123.456'],
            [2, '123.45a'],
            [2, 'a123.45'],
            [3, '123.4567'],
            [0, '123.1'],
            [0, '-123.1'],
        ];
    }

    public function testInvalidPlaces()
    {
        $this->expectException(InvalidArgumentException::class);
        new DecimalRule(-1);
    }
}
