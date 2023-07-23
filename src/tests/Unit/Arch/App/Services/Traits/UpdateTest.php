<?php

namespace Tests\Unit\App\Services\Traits;

use ArchCrudLaravel\App\Enums\Http\StatusCode;
use ArchCrudLaravel\App\Exceptions\UpdateException;
use ArchCrudLaravel\App\Models\Tests\{
    RelationsModel,
    TestsModel
};
use ArchCrudLaravel\App\Providers\ArchProvider;
use ArchCrudLaravel\App\Services\Traits\Update;
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

class UpdateTest extends TestCase
{
    use Update, RemoveMigrations;

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
        $this->nameModel = TestsModel::class;
        $this->model = new $this->nameModel;
        $this->relationships = ['relation'];
        $this->nameModel::create([
            'key' => 'initial',
            'value' => 'target for update',
        ]);
        $this->request = [
            'key' => 'test key update',
            'value' => 'test value update'
        ];
        $this->id = 1;
    }

    protected function tearDown(): void
    {
        $migrator = app('migrator');
        $migrator->rollback([database_path('migrations')]);
        $this->removeMigrations();
        parent::tearDown();
    }

    public function testUpdateSuccess()
    {
        $response = $this->update($this->request, $this->id);
        $body = json_decode($response->getContent());
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(StatusCode::OK->value, $response->getStatusCode());
        $this->assertEquals($this->request['key'], $body->key);
        $this->assertEquals($this->request['value'], $body->value);
    }

    public function testUpdateEmptyRequest()
    {
        $response = $this->update([], $this->id);
        $body = $response->getContent();
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(StatusCode::BAD_REQUEST->value, $response->getStatusCode());
        $this->assertEquals('exceptions.error.update', $body);
    }

    public function testBeforeModify()
    {
        $this->beforeModify();
        $this->assertInstanceOf(UpdateTest::class, $this);
    }

    public function testModifySuccess()
    {
        $this->register = $this->model->findOrFail($this->id);
        $this->modify();
        $this->assertEquals($this->request['key'], $this->register->key);
        $this->assertEquals($this->request['value'], $this->register->value);
    }

    public function testModifyError()
    {
        $this->request = [];
        $this->expectException(UpdateException::class);
        $this->modify();
    }

    public function testAfterModify()
    {
        $this->afterModify();
        $this->assertInstanceOf(UpdateTest::class, $this);
    }

    public function testReactivate()
    {
        $this->register = new TestsModel;
        $this->register->key = 'reactivation';
        $this->register->value = 'target';
        $this->register->deleted_at = '2023-06-01 12:00:00';
        $this->register->save();
        $this->reactivate();
        $this->assertNull($this->register->deleted_at);
    }

    public function testProcessReactivateOnRelatedItems()
    {
        RelationsModel::create([
            'test_id' => $this->id,
            'deleted_at' => '2023-06-01 12:00:00'
        ]);
        $this->register = TestsModel::findOrFail($this->id);
        $this->register->deleted_at = '2023-06-01 12:00:00';
        $this->register->save();
        $this->processReactivateOnRelatedItems($this->register);
        $this->assertNull($this->register->relation->deleted_at);
    }

    public function testReactivateRelatedItems()
    {
        $relatedItem = RelationsModel::create([
            'test_id' => $this->id,
            'deleted_at' => '2023-06-01 12:00:00'
        ]);
        $this->register = TestsModel::findOrFail($this->id);
        $this->register->deleted_at = '2023-06-01 12:00:00';
        $this->register->save();
        $this->reactivateRelatedItems($relatedItem);
        $this->assertNull($relatedItem->deleted_at);
    }
}