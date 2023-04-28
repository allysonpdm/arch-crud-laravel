<?php

namespace ArchCrudLaravel\Tests\Unit\Arch\App\Services\Traits;

use ArchCrudLaravel\App\Models\Tests\{
    RelationsModel,
    TestsModel
};
use ArchCrudLaravel\App\Services\Traits\ShowRegister;
use ArchCrudLaravel\App\Providers\ArchProvider;
use ArchCrudLaravel\App\Http\Resources\Tests\TestResource;
use ArchCrudLaravel\Tests\Traits\MigrationControl;
use Illuminate\Database\Eloquent\{
    Builder,
    Model
};
use Illuminate\Support\Facades\{
    App,
    DB
};
use Tests\TestCase;

class ShowRegisterTest extends TestCase
{
    protected TestsModel $testModel;
    protected RelationsModel $relationModel;
    
    use ShowRegister, MigrationControl;

    protected function setUp(): void
    {
        parent::setUp();

        // Publica a migration
        $this->executeMigration(ArchProvider::class);

        // Configuração inicial
        $this->model = new TestsModel;
        $this->request = [
            'key' => 'test Show Register By Id',
            'value' => 'test Show Register'
        ];
        $this->relationships = ['relation'];

        // Cria um registro de teste
        $this->testModel = TestsModel::create($this->request);
        RelationsModel::create(['test_id' => $this->testModel->id]);
        $this->relationModel = RelationsModel::find($this->testModel->id);
    }

    public function testShowRegisterById()
    {
        // Testa a busca por ID
        $result = $this->showRegister($this->testModel->id);
        $this->assertInstanceOf(get_class($this->model), $result);
        $this->assertEquals($this->testModel->id, $result->id);
        $this->assertEquals($this->request['key'], $result->key);
        $this->assertEquals($this->request['value'], $result->value);
        $this->assertEquals($this->relationModel, $result->relation);
    }

    public function testShowRegisterByRequest()
    {
        // Testa a busca por request
        $result = $this->showRegister();
        $this->assertInstanceOf(get_class($this->model), $result);
        $this->assertEquals($this->request['key'], $result->first()->key);
        $this->assertEquals($this->request['value'], $result->first()->value);
        $this->assertEquals($this->relationModel, $result->first()->relation);
    }

    public function testShowRegisterWithResource()
    {
        // Configura a classe de recurso
        $this->nameResource = TestResource::class;

        // Testa a busca com recurso
        $result = $this->showRegister($this->testModel->id);
        $this->assertInstanceOf($this->nameResource, $result);
        $this->assertEquals($this->testModel->id, $result->id);
        $this->assertEquals($this->request['key'], $result->key);
        $this->assertEquals($this->request['value'], $result->value);
    }
}
