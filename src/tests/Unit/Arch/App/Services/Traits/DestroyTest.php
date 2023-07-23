<?php

namespace Tests\Unit\App\Services\Traits;

use ArchCrudLaravel\App\Enums\Http\StatusCode;
use ArchCrudLaravel\App\Exceptions\SoftDeleteException;
use ArchCrudLaravel\App\Models\Tests\{
    RelationsModel,
    TestsModel
};
use ArchCrudLaravel\App\Providers\ArchProvider;
use ArchCrudLaravel\App\Services\Traits\Destroy;
use ArchCrudLaravel\Tests\Traits\RemoveMigrations;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\{
    Artisan,
    DB,
    Schema
};
use Tests\TestCase;

class DestroyTest extends TestCase
{
    use Destroy, RemoveMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        // Publica a migration
        Artisan::call('vendor:publish', [
            '--provider' => ArchProvider::class,
            '--tag' => 'migrations'
        ]);
        Artisan::call('migrate');

        // Configuração inicial
        $this->nameModel = TestsModel::class;
        $this->model = $this->nameModel::create([
            'key' => 'initial',
            'value' => 'target for delete',
        ]);
        $this->now = '2023-06-01 12:00:00';
    }

    protected function tearDown(): void
    {
        $migrator = app('migrator');
        $migrator->rollback([database_path('migrations')]);
        $this->removeMigrations();
        parent::tearDown();
    }

    public function testDestroySuccess()
    {
        $response = $this->destroy([], $this->model->id);
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(StatusCode::OK->value, $response->getStatusCode());
        $this->assertEquals('O registro foi desabilitado.', $response->getContent());
    }

    public function testDestroyWithForce()
    {
        $response = $this->destroy(['force' => true], $this->model->id);
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(StatusCode::OK->value, $response->getStatusCode());
        $this->assertEquals('O registro e os vínculos foram excluídos definitivamente.', $response->getContent());
    }

    public function testDestroyNotFound()
    {
        $response = $this->destroy([], 999);
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(StatusCode::NOT_FOUND->value, $response->getStatusCode());
        $this->assertEquals('{"Message":"exceptions.error.no_results"}', $response->getContent());
    }

    public function testDestroySoftDeleteException()
    {
        $model = TestsModel::find($this->model->id);
        $model->deleted_at = $this->now;
        $model->save();

        $response = $this->destroy([], $this->model->id);
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(StatusCode::OK->value, $response->getStatusCode());
        $this->assertEquals('exceptions.error.soft_delete', $response->getContent());
    }

    public function testBeforeDelete()
    {
        $this->beforeDelete();
        $this->assertInstanceOf(DestroyTest::class, $this);
    }

    public function testDeleteSuccess()
    {
        $this->register = $this->model->findOrFail($this->model->id);
        $response = $this->deleteRecord();
        $this->assertInstanceOf(DestroyTest::class, $response);
    }
    public function testAfterDelete()
    {
        $this->afterDelete();
        $this->assertInstanceOf(DestroyTest::class, $this);
    }
}
