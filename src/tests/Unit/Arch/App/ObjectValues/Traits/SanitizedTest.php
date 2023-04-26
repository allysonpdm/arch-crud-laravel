<?php

namespace ArchCrudLaravel\Tests\Unit\Arch\App\ObjectValues\Traits;

use ArchCrudLaravel\App\ObjectValues\Traits\Sanitized;
use ArchCrudLaravel\App\ObjectValues\Regex;
use Tests\TestCase;

class SanitizedTest extends TestCase
{
    protected string $regex;
    protected mixed $value;

    use Sanitized;

    public function testSetRegex()
    {
        $this->value = 'a1b2c3';
        $this->setRegex(new Regex('[0-9]'));
        
        $this->assertEquals('/[0-9]/', $this->regex);
    }

    /**
     * @dataProvider sanitizedValueProvider
     */
    public function testSanitized($value, $regex, $expected)
    {
        $this->value = $value;
        $this->setRegex(new Regex($regex));
        $formattedValue = $this->sanitized();

        $this->assertEquals($expected, $formattedValue);
    }

    public function sanitizedValueProvider()
    {
        return [
            ['a1b2c3', '[a-z]', '123'],
            ['a1b2c3', '[0-9]', 'abc'],
            ['999.999.999-99', '.-', '99999999999'],
            ['32.631.433/0001-31', './-', '32631433000131'],
            ['Anapólis', 'ó', 'Anaplis'],
        ];
    }
}
