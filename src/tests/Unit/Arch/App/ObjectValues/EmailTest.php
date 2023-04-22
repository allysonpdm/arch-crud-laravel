<?php

namespace ArchCrudLaravel\Tests\Unit\Arch\App\ObjectValues;

use ArchCrudLaravel\App\ObjectValues\Email;
use InvalidArgumentException;
use Tests\TestCase;

class EmailTest extends TestCase
{
    public function testValidEmail()
    {
        $email = new Email('test@example.com');

        $this->assertEquals('test@example.com', (string) $email);
    }

    public function testInvalidEmail()
    {
        $this->expectException(InvalidArgumentException::class);

        new Email('invalid_email');
    }

    public function testEmailToLowercase()
    {
        $email = new Email('TEST@EXAMPLE.COM');

        $this->assertEquals('test@example.com', (string) $email);
    }
}
