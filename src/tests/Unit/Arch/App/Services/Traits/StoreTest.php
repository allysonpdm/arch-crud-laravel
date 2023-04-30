<?php

namespace Tests\Unit\App\Services\Traits;

use ArchCrudLaravel\App\Exceptions\CreateException;
use ArchCrudLaravel\App\Models\Tests\{
    RelationsModel,
    TestsModel
};
use ArchCrudLaravel\App\Services\Traits\Store;
use ArchCrudLaravel\App\Tests\Traits\RemoveMigrations;
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

        // Executa a migration
        $this->artisan('migrate');

        // Configuração inicial
        $this->model = new TestsModel;
        $this->request = [
            'key' => 'test key store',
            'value' => 'test value storage'
        ];
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
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals(1, TestsModel::count());
        $this->assertEquals('test key store', TestsModel::first()->key);
        $this->assertEquals('test value storage', TestsModel::first()->value);
    }

    public function testStoreEmptyRequest()
    {
        $this->expectException(CreateException::class);
        $this->store([]);
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
        $this->assertEquals('test', TestsModel::first()->key);
        $this->assertEquals('test', TestsModel::first()->value);
    }

    public function testInsertError()
    {
        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('rollBack')->once();
        $this->model = new TestsModel();
        $this->expectException(CreateException::class);
        $this->insert();
    }

    public function testAfterInsert()
    {
        $this->afterInsert();
        $this->assertInstanceOf(StoreTest::class, $this);
    }
}
