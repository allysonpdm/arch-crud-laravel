<?php

namespace App\Exceptions;

use Exception;

class TableNotFoundException extends Exception
{
    protected $code = 500;

    public function __construct()
    {
        $this->message = __('exceptions.error.table_not_found');
    }

    public function render(){}
}
