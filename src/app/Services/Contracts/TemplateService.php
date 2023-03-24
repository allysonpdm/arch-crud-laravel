<?php

namespace ArchCrudLaravel\App\Services\Contracts;

use Illuminate\Http\Response;

interface TemplateService
{
    public function index(array $request): Response;
    public function show(array $request, string|int $id): Response;
    public function store(array $request): Response;
    public function update(array $request, string|int $id): Response;
    public function destroy(array $request, string|int $id): Response;
}
