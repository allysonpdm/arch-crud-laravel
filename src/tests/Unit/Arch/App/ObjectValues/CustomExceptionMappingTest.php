<?php

namespace ArchCrudLaravel\Tests\Unit\Arch\App\ObjectValues;

use ArchCrudLaravel\App\ObjectValues\CustomExceptionMapping;
use Exception;
use InvalidArgumentException;
use Tests\TestCase;

class CustomExceptionMappingTest extends TestCase
{
    public function testValidCustomExceptionMapping()
    {
        $customExceptionMapping = new CustomExceptionMapping(InvalidArgumentException::class, function ($e) {
            return 'Error: ' . $e->getMessage();
        });

        $this->assertCount(1, $customExceptionMapping);
    }

    public function testInvalidExceptionClass()
    {
        $this->expectException(InvalidArgumentException::class);

        new CustomExceptionMapping('InvalidExceptionClass', function ($e) {
            return 'Error: ' . $e->getMessage();
        });
    }

    public function testInvalidHandler()
    {
        $this->expectException(InvalidArgumentException::class);

        new CustomExceptionMapping(Exception::class, 'TypeError');
    }

    public function testArrayAccess()
    {
        $customExceptionMapping = new CustomExceptionMapping(Exception::class, function ($e) {
            return 'Error: ' . $e->getMessage();
        });

        $this->assertTrue(isset($customExceptionMapping[Exception::class]));
        $this->assertInstanceOf(\Closure::class, $customExceptionMapping[Exception::class]);
    }

    public function testArrayAccessReadOnly()
    {
        $this->expectException(Exception::class);

        $customExceptionMapping = new CustomExceptionMapping(Exception::class, function ($e) {
            return 'Error: ' . $e->getMessage();
        });

        $customExceptionMapping[Exception::class] = function ($e) {
            return 'Error: ' . $e->getMessage();
        };
    }

    public function testCountable()
    {
        $customExceptionMapping = new CustomExceptionMapping(Exception::class, function ($e) {
            return 'Error: ' . $e->getMessage();
        });

        $this->assertEquals(1, count($customExceptionMapping));
    }

    public function testIteratorAggregate()
    {
        $customExceptionMapping = new CustomExceptionMapping(Exception::class, function ($e) {
            return 'Error: ' . $e->getMessage();
        });

        foreach ($customExceptionMapping as $exceptionClass => $handler) {
            $this->assertEquals(Exception::class, $exceptionClass);
            $this->assertInstanceOf(\Closure::class, $handler);
        }
    }
    
    public function testToArray()
    {
        $customExceptionMapping = new CustomExceptionMapping(Exception::class, function ($e) {
            return 'Error: ' . $e->getMessage();
        });

        $array = $customExceptionMapping->toArray();

        $this->assertArrayHasKey(Exception::class, $array);
        $this->assertInstanceOf(\Closure::class, $array[Exception::class]);
    }
}
