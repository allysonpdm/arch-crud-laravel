<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SeederBase extends Seeder
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
}
