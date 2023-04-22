<?php

namespace ArchCrudLaravel\Tests\Unit\Arch\App\Services\Traits;

use Illuminate\Support\Facades\Cache;
use ArchCrudLaravel\App\Services\Traits\CacheControl;
use Tests\TestCase;

class CacheControlTest extends TestCase
{
    use CacheControl;

    protected function setUp(): void
    {
        parent::setUp();

        $this->onCache = true;
        $this->nameModel = 'ExampleModel';
        $this->request = ['id' => 1];
    }

    public function testPutCache()
    {
        $this->putCache('test_key', 'test_value', 10);
        $this->assertEquals('test_value', Cache::get('test_key'));
    }

    public function testGetCache()
    {
        Cache::put('test_key', 'test_value', 10);
        $value = $this->getCache('test_key');
        $this->assertEquals('test_value', $value);
    }

    public function testForgetCache()
    {
        Cache::put('test_key', 'test_value', 10);
        $this->assertTrue($this->forgetCache('test_key'));
        $this->assertNull(Cache::get('test_key'));
    }

    public function testCreateCacheKey()
    {
        $key1 = $this->createCacheKey();
        $key2 = $this->createCacheKey(1, ['foo' => 'bar']);
        $key3 = $this->createCacheKey(null, ['foo' => 'baz']);
        $key4 = $this->createCacheKey(2);

        $this->assertEquals(md5('ExampleModel' . ''), $key1);
        $this->assertEquals(md5('ExampleModel1{"foo":"bar"}'), $key2);
        $this->assertEquals(md5('ExampleModel{"foo":"baz"}'), $key3);
        $this->assertEquals(md5('ExampleModel2'), $key4);
    }
}
