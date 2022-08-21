<?php

namespace ArchCrudLaravel\Database\Seeders;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

abstract class BaseSeeder extends Seeder
{
    protected $data;
    protected $table;
    protected $size = 500;
    protected $database = 'mysql';
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //$this->call([]);
        $this->populate($this->table, $this->data);
    }

    protected function chunk($arr){
        return array_chunk($arr, $this->size);
    }

    protected function populate($table, $data){
        foreach ($this->chunk($data) as $chunk) {
            DB::connection($this->database)->table($table)->insert($chunk);
        }
    }

    protected function getDataFromModel(Model $model):  void
    {
        $this->data = $this->getSeeder($model);
    }

    protected function getSeeder($model){
        $data = [];

        $seeds = $model::SEEDER;

        if(is_array($seeds) || is_object($seeds)){
            foreach ($seeds as $key => $value) {
                is_array($value)
                    ? array_push($data, $value)
                    : array_push($data, self::arrayable(id:$value, descricao:  $key));
            }
        }

        if(is_string($seeds)){
            $enums = $seeds::cases();
            foreach ($enums as $enum) {
                array_push($data, self::arrayable(id: $enum->value, descricao: $enum->name));
            }
        }

        return $data;
    }

    protected static function arrayable(int|string $id, int|string $descricao): array
    {
        return  [
            'id' => $id,
            'descricao' => Str::replace('_', ' ', Str::ucfirst(Str::lower($descricao)))
        ];
    }
}
