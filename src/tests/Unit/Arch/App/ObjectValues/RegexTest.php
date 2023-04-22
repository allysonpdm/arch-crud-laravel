<?php

namespace ArchCrudLaravel\Tests\Unit\Arch\App\ObjectValues;

use ArchCrudLaravel\App\ObjectValues\Regex;
use InvalidArgumentException;
use Tests\TestCase;

class RegexTest extends TestCase
{
    public function testValidRegex()
    {
        $regex = new Regex('\d{3}-\d{2}-\d{4}');

        $this->assertEquals('/\d{3}-\d{2}-\d{4}/', (string) $regex);
    }

    public function testInvalidRegex()
    {
        $this->expectException(InvalidArgumentException::class);

        new Regex(['invalid']);
    }

    public function testRegexWithOptions()
    {
        $regex = new Regex(
            value: '\d{3}-\d{2}-\d{4}',
            global: true,
            multiLine: true,
            insensitive: true
        );

        $this->assertEquals('/\d{3}-\d{2}-\d{4}/gmi', (string) $regex);
    }
}
