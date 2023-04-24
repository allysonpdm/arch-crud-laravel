<?php

namespace ArchCrudLaravel\Tests\Unit\Arch\App\ObjectValues;

use ArchCrudLaravel\App\ObjectValues\Uri;
use InvalidArgumentException;
use Tests\TestCase;

class UriTest extends TestCase
{
    /**
     * @dataProvider validProvider
     */
    public function testValid($value)
    {
        $uri = new Uri($value);

        $this->assertEquals($value, (string) $uri);
    }

    public function validProvider()
    {
        return [
            ['https://www.example.com'],
            ['http://localhost:8000'],
            ['ftp://ftp.example.com'],
            ['https://www.example.com/path/to/resource?param=value#fragment'],
        ];
    }

    /**
     * @dataProvider invalidProvider
     */
    public function testInvalid($value)
    {
        $this->expectException(InvalidArgumentException::class);

        new Uri($value);
    }

    public function invalidProvider()
    {
        return [
            ['invalid-url'],
            ['example.com'],
            ['http:/example.com'],
            ['https://www.example.com?query=invalid&param=value'],
        ];
    }

    public function testToString()
    {
        $uri = new Uri('https://www.example.com/path/to/resource?param=value#fragment');

        $this->assertEquals('https://www.example.com/path/to/resource?param=value#fragment', $uri->__toString());
    }
}
