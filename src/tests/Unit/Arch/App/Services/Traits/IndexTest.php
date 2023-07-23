<?php

namespace Tests\Unit\App\Services\Traits;

use ArchCrudLaravel\App\Enums\Http\StatusCode;
use ArchCrudLaravel\App\Models\Tests\RelationsModel;
use ArchCrudLaravel\App\Models\Tests\TestsModel;
use ArchCrudLaravel\App\Providers\ArchProvider;
use ArchCrudLaravel\App\Services\Traits\Index;
use ArchCrudLaravel\Tests\Traits\RemoveMigrations;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use Index, RemoveMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        // Publica a migration
        $this->artisan('vendor:publish', [
            '--provider' => ArchProvider::class,
            '--tag' => 'migrations'
        ]);
        $this->artisan('migrate');

        for ($i = 1; $i <= 15; $i++) {
            TestsModel::create([
                'key' => 'test key ' . $i,
                'value' => 'test value ' . $i
            ]);
        }

        // Configuração inicial
        $this->nameModel = TestsModel::class;
        $this->relationships = ['relation'];
        $this->model = new $this->nameModel;
        $this->request = [
            'perPage' => 10,
            'page' => 1,
            'wheres' => [
                [
                    'column' => 'key',
                    'condition' => '=',
                    'search' => 'test key'
                ],
                [
                    'column' => 'value',
                    'condition' => 'like',
                    'search' => '%test%'
                ]
            ],
            'orWheres' => [
                [
                    'column' => 'key',
                    'condition' => '!=',
                    'search' => 'another key'
                ]
            ],
            'orderBy' => [
                'key' => 'asc',
                'value' => 'desc'
            ]
        ];

    }

    protected function tearDown(): void
    {
        $migrator = app('migrator');
        $migrator->rollback([database_path('migrations')]);
        $this->removeMigrations();
        parent::tearDown();
    }

    public function testIndexSuccess()
    {
        $response = $this->index($this->request);
        $body = json_decode($response->getContent());
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(StatusCode::OK->value, $response->getStatusCode());
        $this->assertCount(10, $body->data);
        $this->assertEquals(15, $body->total);
        $this->assertEquals(10, $body->per_page);
        $this->assertEquals(1, $body->current_page);
        $this->assertIsString($body->next_page_url);
        $this->assertNull($body->prev_page_url);
        $this->assertEquals(2, $body->last_page);
        $this->assertEquals(1, $body->from);
        $this->assertEquals(10, $body->to);
    }

    public function testIndexEmpty()
    {
        $this->request = [
            ...$this->request,
            'wheres' => [
                [
                    'column' => 'key',
                    'condition' => '=',
                    'search' => 100
                ]
            ],
            'orWheres' => null
        ];
        $response = $this->index($this->request);
        $body = json_decode($response->getContent());
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(StatusCode::NOT_FOUND->value, $response->getStatusCode());
        $this->assertIsObject($body);
        $this->assertObjectHasAttribute('Message', $body);
        $this->assertEquals('exceptions.error.no_results', $body->Message);
    }

    public function testBeforeList()
    {
        $result = $this->beforeList();
        $this->assertInstanceOf(IndexTest::class, $result);
    }

    public function testList()
    {
        $result = $this->list();
        $this->assertInstanceOf(IndexTest::class, $result);
    }

    public function testSearch()
    {
        $result = $this->search(null, null);
        $this->assertInstanceOf(IndexTest::class, $result);
        $this->assertCount(1, $result);
    }

    public function testOrdenation()
    {
        $result = $this->ordenation(null);
        $this->assertInstanceOf(IndexTest::class, $result);
        $this->assertEquals('asc', $this->request['orderBy']['key']);
        $this->assertEquals('desc', $this->request['orderBy']['value']);
    }

    public function testAfterList()
    {
        $result = $this->afterList();
        $this->assertInstanceOf(IndexTest::class, $result);
    }

    public function testPaginate()
    {
        $this->model = null;
        $items = TestsModel::all();
        $result = $this->paginate($items);
        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
    }
}