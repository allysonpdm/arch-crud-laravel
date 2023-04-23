<?php

namespace ArchCrudLaravel\Tests\Unit\Arch\App\Rules;

use ArchCrudLaravel\App\Rules\UriRule;
use Tests\TestCase;

class UriRuleTest extends TestCase
{
    /**
     * @dataProvider validUriDataProvider
     */
    public function testValidUri($uri)
    {
        $rule = new UriRule();
        $this->assertTrue($rule->passes('uri', $uri));
    }

    /**
     * @dataProvider invalidUriDataProvider
     */
    public function testInvalidUri($uri)
    {
        $rule = new UriRule();
        $this->assertFalse($rule->passes('uri', $uri));
    }

    public function validUriDataProvider()
    {
        return [
            ['https://www.example.com'],
            ['http://www.example.com'],
            ['https://example.com/test'],
            ['ftp://example.com'],
        ];
    }

    public function invalidUriDataProvider()
    {
        return [
            ['example.com'],
            ['http:/www.example.com'],
            ['https:example.com'],
            [''],
            [null],
        ];
    }
}
