<?php

namespace ArchCrudLaravel\Tests\Unit\Arch\App\Services\Traits;

use ArchCrudLaravel\App\Services\Traits\ShowRegister;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ShowRegisterTest extends TestCase
{
    use ShowRegister;

    protected function setUp(): void
    {
        parent::setUp();

        // Configuração inicial
        $this->model = new class extends Model {
            protected $table = 'tests';
        };
        $this->request = ['testShowRegister' => 'valueTeste'];
        $this->relationships = ['relationship'];
    }

    public function testShowRegisterById()
    {
        // Cria um registro de teste
        $id = DB::table('tests')->insertGetId([
            'column' => 'value',
            'relationship' => 'related_value',
        ]);

        // Testa a busca por ID
        $result = $this->showRegister($id);
        $this->assertInstanceOf(get_class($this->model), $result);
        $this->assertEquals($id, $result->id);
        $this->assertEquals('value', $result->column);
        $this->assertEquals('related_value', $result->relationship);
    }

    public function testShowRegisterByRequest()
    {
        // Cria um registro de teste
        DB::table('tests')->insert([
            'column' => 'value',
            'relationship' => 'related_value',
        ]);

        // Testa a busca por request
        $result = $this->showRegister();
        $this->assertInstanceOf(Builder::class, $result);
        $this->assertEquals('value', $result->first()->column);
        $this->assertEquals('related_value', $result->first()->relationship);
    }

    public function testShowRegisterWithResource()
    {
        // Cria um registro de teste
        $id = DB::table('tests')->insertGetId([
            'column' => 'value',
            'relationship' => 'related_value',
        ]);

        // Configura a classe de recurso
        $this->nameResource = new class($this->model) extends \Illuminate\Http\Resources\Json\JsonResource {
            public function toArray($request)
            {
                return [
                    'id' => $this->id,
                    'column' => $this->column . '_formatted',
                    'relationship' => $this->relationship . '_formatted',
                ];
            }
        };

        // Testa a busca com recurso
        $result = $this->showRegister($id);
        $this->assertInstanceOf(get_class($this->nameResource), $result);
        $this->assertEquals($id, $result->id);
        $this->assertEquals('value_formatted', $result->column);
        $this->assertEquals('related_value_formatted', $result->relationship);
    }
}
