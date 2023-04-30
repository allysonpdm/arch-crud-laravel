<?php

namespace Tests\Unit\App\Services\Traits;

use ArchCrudLaravel\App\Enums\Http\StatusCode;
use ArchCrudLaravel\App\Exceptions\NotFoundException;
use ArchCrudLaravel\App\Models\Tests\TestsModel;
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
        $this->request = [];
        $this->relationships = ['relation'];
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
        $test = TestsModel::factory()->create();
        $response = $this->show([], $test->id);
        $body = json_decode($response->getContent());
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(StatusCode::OK->value, $response->getStatusCode());
        $this->assertEquals($test->id, $body->id);
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
