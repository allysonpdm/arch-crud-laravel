<?php

namespace App\Rules;

use App\Exceptions\TableNotFoundException;
use Exception;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class FieldsExistsInTableRule implements Rule
{
    private $columns;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(string $table)
    {
        $this->table= $table;
        $this->columns = Schema::getColumnListing($table);
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if(empty($this->columns))
            throw new TableNotFoundException;

        if(is_string($value)){
           return $this->existsInDataBase($value);
        }else{
            foreach($value as $key => $val){
                return $this->existsInDataBase($key);
            }
        }
    }

    private function existsInDataBase($value): bool
    {
        if(!in_array($value, $this->columns)){
            $this->notFoundField = $value;
            return false;
        }
        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('validation.field_not_exists_in_table', [
            'field' => $this->notFoundField,
            'table' => $this->table
        ]);
    }
}
