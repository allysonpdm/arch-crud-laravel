<?php

namespace ArchCrudLaravel\Tests\Unit\Arch\App\Services\Traits;

use ArchCrudLaravel\App\Models\Tests\{
    RelationsModel,
    TestsModel
};
use ArchCrudLaravel\App\Services\Traits\ShowRegister;
use ArchCrudLaravel\App\Providers\ArchProvider;
use ArchCrudLaravel\App\Http\Resources\Tests\TestResource;
use ArchCrudLaravel\Tests\Traits\RemoveMigrations;
use Illuminate\Database\Eloquent\{
    Builder,
    Model
};
use Illuminate\Support\Facades\{
    App,
    DB
};
use Tests\TestCase;
use FilesystemIterator;

class ShowRegisterTest extends TestCase
{
    protected TestsModel $testModel;
    protected RelationsModel $relationModel;

    use ShowRegister, RemoveMigrations;

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
            'key' => 'test Show Register By Id',
            'value' => 'test Show Register'
        ];
        $this->relationships = ['relation'];

        $this->testModel = TestsModel::create($this->request);
        RelationsModel::create(['test_id' => $this->testModel->id]);
        $this->relationModel = RelationsModel::find($this->testModel->id);
    }

    protected function tearDown(): void
    {
        $migrator = app('migrator');
        $migrator->rollback([database_path('migrations')]);
        $this->removeMigrations();
        parent::tearDown();
    }

    public function testShowRegisterById()
    {
        $result = $this->showRegister($this->testModel->id);
        $this->assertInstanceOf(get_class($this->model), $result);
        $this->assertEquals($this->testModel->id, $result->id);
        $this->assertEquals($this->request['key'], $result->key);
        $this->assertEquals($this->request['value'], $result->value);
        $this->assertEquals($this->relationModel, $result->relation);
    }

    public function testShowRegisterByRequest()
    {
        $result = $this->showRegister();
        $this->assertInstanceOf(get_class($this->model), $result);
        $this->assertEquals($this->request['key'], $result->first()->key);
        $this->assertEquals($this->request['value'], $result->first()->value);
    }

    public function testShowRegisterWithResource()
    {
        $this->nameResource = TestResource::class;
        $result = $this->showRegister($this->testModel->id);
        $this->assertIsArray($result);
        $this->assertEquals($this->testModel->id, $result['id']);
        $this->assertEquals($this->request['key'], $result['key']);
        $this->assertEquals($this->request['value'], $result['value']);
        $this->assertEquals($this->relationModel->toArray(), $result['relation']);
    }

}
