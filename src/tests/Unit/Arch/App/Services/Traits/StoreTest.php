<?php

namespace Tests\Unit\App\Services\Traits;

use ArchCrudLaravel\App\Exceptions\CreateException;
use ArchCrudLaravel\App\Models\Tests\{
    RelationsModel,
    TestsModel
};
use ArchCrudLaravel\App\Providers\ArchProvider;
use ArchCrudLaravel\App\Services\Traits\Store;
use ArchCrudLaravel\Tests\Traits\RemoveMigrations;
use Illuminate\Database\Eloquent\{
    Builder,
    Model
};
use Illuminate\Http\Response;
use Illuminate\Support\Facades\{
    DB,
    Schema
};
use Tests\TestCase;

class StoreTest extends TestCase
{
    use Store, RemoveMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        // Publica a migration
        $this->artisan('vendor:publish', [
            '--provider' => ArchProvider::class,
            '--tag' => 'migrations'
        ]);
        $this->artisan('migrate');

        // Configuração inicial
        $this->model = new TestsModel;
        $this->request = [
            'key' => 'test key store',
            'value' => 'test value storage'
        ];
        $this->relationships = ['relation'];
    }

    protected function tearDown(): void
    {
        $migrator = app('migrator');
        $migrator->rollback([database_path('migrations')]);
        $this->removeMigrations();
        parent::tearDown();
    }


    public function testStoreSuccess()
    {
        $response = $this->store($this->request);
        $body = json_decode($response->getContent());
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals(1, TestsModel::count());
        $this->assertEquals($this->request['key'], $body->key);
        $this->assertEquals($this->request['value'], $body->value);
    }

    public function testStoreEmptyRequest()
    {
        $response = $this->store([]);
        $body = $response->getContent();
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(500, $response->getStatusCode());
        $this->assertEquals('exceptions.error.create', $body);
    }

    public function testBeforeInsert()
    {
        $this->beforeInsert();
        $this->assertInstanceOf(StoreTest::class, $this);
    }

    public function testInsertSuccess()
    {
        $this->insert();
        $this->assertEquals(1, TestsModel::count());
        $this->assertEquals($this->request['key'], $this->model->key);
        $this->assertEquals($this->request['value'], $this->model->value);
    }

    public function testInsertError()
    {
        $this->request = [];
        $this->expectException(CreateException::class);
        $this->insert();
    }

    public function testAfterInsert()
    {
        $this->afterInsert();
        $this->assertInstanceOf(StoreTest::class, $this);
    }

}
