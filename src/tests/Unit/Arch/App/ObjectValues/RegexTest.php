<?php

namespace ArchCrudLaravel\Tests\Unit\Arch\App\ObjectValues;

use ArchCrudLaravel\App\ObjectValues\Regex;
use InvalidArgumentException;
use Tests\TestCase;

class RegexTest extends TestCase
{
    /**
     * @dataProvider validProvider
     */
    public function testValid($value)
    {
        $regex = new Regex($value);

        $this->assertEquals('/' . $value . '/', (string) $regex);
    }

    public function validProvider()
    {
        return [
            ['\d{3}-\d{2}-\d{4}'],
            ['^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$'],
            ['[0-9a-f]{8}(-[0-9a-f]{4}){3}-[0-9a-f]{12}'],
        ];
    }

    /**
     * @dataProvider invalidProvider
     */
    public function testInvalid($value)
    {
        $this->expectException(InvalidArgumentException::class);

        new Regex($value);
    }

    public function invalidProvider()
    {
        return [
            ['(missing closing bracket'],
            ['[invalid character]'],
            ['\d{3}-\d{2}-\d{4}/'],
        ];
    }

    public function testWithOptions()
    {
        $regex = new Regex(
            value: '\d{3}-\d{2}-\d{4}',
            global: true,
            multiLine: true,
            insensitive: true
        );

        $this->assertEquals('/\d{3}-\d{2}-\d{4}/mig', (string) $regex);
    }
}
