<?php

namespace Database\Seeders;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

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
        $this->data = self::getTipos($model);
    }

    protected static function getTipos($model){
        $data = [];
        foreach ($model::SEEDER as $key => $value) {
            is_array($value)
                ? array_push($data, $value)
                : array_push($data, ['id' => $value, 'descricao' => ucfirst($key)]);
        }
        return $data;
    }
}
