<?php

namespace ArchCrudLaravel\Tests\Unit\Arch\App\ObjectValues;

use ArchCrudLaravel\App\ObjectValues\Uri;
use InvalidArgumentException;
use Tests\TestCase;

class UriTest extends TestCase
{
    public function testValidUri()
    {
        $uri = new Uri('https://www.example.com');

        $this->assertEquals('https://www.example.com', (string) $uri);
    }

    public function testInvalidUri()
    {
        $this->expectException(InvalidArgumentException::class);

        new Uri('invalid-url');
    }

    public function testUriToString()
    {
        $uri = new Uri('https://www.example.com/path/to/resource?param=value#fragment');

        $this->assertEquals('https://www.example.com/path/to/resource?param=value#fragment', $uri->__toString());
    }
}
