<?php

namespace ArchCrudLaravel\Tests\Unit\Arch\App\Rules;

use ArchCrudLaravel\App\Rules\UriRule;
use Tests\TestCase;

class UriRuleTest extends TestCase
{
    /**
     * @dataProvider validDataProvider
     */
    public function testValid($uri)
    {
        $rule = new UriRule();
        $this->assertTrue($rule->passes('uri', $uri));
    }

    public function validDataProvider()
    {
        return [
            ['https://www.example.com'],
            ['http://www.example.com'],
            ['https://example.com/test'],
            ['ftp://example.com'],
        ];
    }

    /**
     * @dataProvider invalidDataProvider
     */
    public function testInvalid($uri)
    {
        $rule = new UriRule();
        $this->assertFalse($rule->passes('uri', $uri));
    }

    public function invalidDataProvider()
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
