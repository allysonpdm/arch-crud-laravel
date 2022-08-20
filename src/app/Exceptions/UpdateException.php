<?php

namespace ArchCrudLaravel\App\Exceptions;

use Exception;

class UpdateException extends Exception
{
    protected $code = 400;

    public function __construct()
    {
        $this->message = __('exceptions.error.update');
    }

    public function render(){}
}
