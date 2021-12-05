<?php

namespace App\Exceptions;

use Exception;

class CreateException extends Exception
{
    protected $code = 500;

    public function __construct()
    {
        $this->message = __('exceptions.error.create');
    }

    public function render(){}
}
