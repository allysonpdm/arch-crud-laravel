<?php

namespace App\Exceptions;

use Exception;

class SoftDeleteException extends Exception
{
    protected $code = 200;

    public function __construct()
    {
        $this->message = __('exceptions.error.soft_delete');
    }

    public function render(){}
}
