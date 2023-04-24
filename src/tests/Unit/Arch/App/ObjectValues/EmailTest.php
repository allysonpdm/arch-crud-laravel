<?php

namespace ArchCrudLaravel\Tests\Unit\Arch\App\ObjectValues;

use ArchCrudLaravel\App\ObjectValues\Email;
use InvalidArgumentException;
use Tests\TestCase;

class EmailTest extends TestCase
{
    public function validProvider()
    {
        return [
            ['test@example.com'],
            ['user123@gmail.com'],
            ['my-email@domain.co.uk'],
            ['test+label@example.com'],
        ];
    }

    /**
     * @dataProvider validProvider
     */
    public function testValid($value)
    {
        $email = new Email($value);

        $this->assertEquals(strtolower($value), (string) $email);
    }

    public function invalidProvider()
    {
        return [
            ['invalid_email'],
            ['not_an_email@'],
            ['user@.domain.com'],
            ['user@domain..com'],
        ];
    }

    /**
     * @dataProvider invalidProvider
     */
    public function testInvalid($value)
    {
        $this->expectException(InvalidArgumentException::class);

        new Email($value);
    }

    public function testToString()
    {
        $email = new Email('TEST@EXAMPLE.COM');

        $this->assertEquals('test@example.com', (string) $email);
    }
}
