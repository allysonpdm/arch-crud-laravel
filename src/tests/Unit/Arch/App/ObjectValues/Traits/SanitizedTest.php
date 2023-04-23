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

    public function testSanitized()
    {
        $this->value = 'a1b2c3';
        $this->setRegex(new Regex('[0-9]'));
        $formattedValue = $this->sanitized();

        $this->assertEquals('abc', $formattedValue);
    }
}
