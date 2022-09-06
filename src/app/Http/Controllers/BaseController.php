<?php

namespace ArchCrudLaravel\App\Http\Controllers;

use ArchCrudLaravel\App\Services\BaseServices;

abstract class BaseController
{
    protected $nameService = BaseServices::class;
    protected $service;

    public function __construct()
    {
        $this->service = new ($this->nameService);
    }
}
