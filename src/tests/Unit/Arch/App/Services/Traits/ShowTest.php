<?php

namespace Tests\Unit\App\Services\Traits;

use ArchCrudLaravel\App\Enums\Http\StatusCode;
use ArchCrudLaravel\App\Exceptions\NotFoundException;
use ArchCrudLaravel\App\Models\Tests\{
    RelationsModel,
    TestsModel
};
use ArchCrudLaravel\App\Providers\ArchProvider;
use ArchCrudLaravel\App\Services\Traits\Show;
use ArchCrudLaravel\Tests\Traits\RemoveMigrations;
use Illuminate\Http\Response;
use Tests\TestCase;

class ShowTest extends TestCase
{
    use Show, RemoveMigrations;

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
            'key' => 'test key show',
            'value' => 'test value show'
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

    public function testShowSuccess()
    {
        $response = $this->show([], $this->testModel->id);
        $body = json_decode($response->getContent());
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(StatusCode::OK->value, $response->getStatusCode());
        $this->assertEquals($this->testModel->id, $body->id);
    }

    public function testShowNotFound()
    {
        $this->expectException(NotFoundException::class);
        $this->show([], 999);
    }

    public function testBeforeSelect()
    {
        $this->beforeSelect();
        $this->assertInstanceOf(ShowTest::class, $this);
    }

    public function testSelectSuccess()
    {
        $test = TestsModel::factory()->create();
        $this->select();
        $this->assertEquals($test->id, $this->model->id);
    }

    public function testSelectNotFound()
    {
        $this->expectException(NotFoundException::class);
        $this->select();
    }

    public function testAfterSelect()
    {
        $this->afterSelect();
        $this->assertInstanceOf(ShowTest::class, $this);
    }
}
