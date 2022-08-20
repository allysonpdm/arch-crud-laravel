<?php

namespace ArchCrudLaravel\App\Exceptions;

use Exception;

class BusinessException extends Exception
{
    protected $code = 500;
}
