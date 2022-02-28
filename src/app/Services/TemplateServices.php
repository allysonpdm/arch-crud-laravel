<?php

namespace App\Services;

use Illuminate\Http\Response;

interface TemplateServices
{
    public function index(array $request): Response;
    public function show(string|int $id): Response;
}
