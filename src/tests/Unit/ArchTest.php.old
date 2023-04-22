<?php

namespace ArchCrudLaravel\Tests\Unit;

use ArchCrudLaravel\App\Models\ArchModel;
use ArchCrudLaravel\App\Services\BaseService;
use Exception;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use ReflectionClass;
#use PHPUnit\Framework\TestCase;
use Tests\TestCase;

class ArchTest extends TestCase
{
    private function returnPrivateMethod($method, $params = [])
    {
        $stub = $this->getMockForAbstractClass(BaseService::class);
        $reflectionClass = new ReflectionClass($stub);
        $method = $reflectionClass->getMethod($method);
        $method->setAccessible(true);
        return $method->invokeArgs($stub, $params);
    }

    private function testReturnPrivateMethod($expected, string $method, array $params = [])
    {
        $return = $this->returnPrivateMethod($method, $params);
        $this->assertInstanceOf($expected, $return);
    }

    public function test_protected_method_before_list()
    {
        $this->testReturnPrivateMethod(BaseService::class, 'beforeList');
    }

    public function test_protected_method_search()
    {
        $this->testReturnPrivateMethod(BaseService::class, 'search', [array(), array()]);
    }

    public function test_protected_method_ordenation()
    {
        $this->testReturnPrivateMethod(BaseService::class, 'ordenation', [array()]);
    }

    public function test_protected_method_list()
    {
        $this->testReturnPrivateMethod(BaseService::class, 'list');
    }

    public function test_protected_method_after_list()
    {
        $this->testReturnPrivateMethod(BaseService::class, 'afterList');
    }

    public function test_protected_method_paginate()
    {
        $this->testReturnPrivateMethod(LengthAwarePaginator::class, 'paginate', [ArchModel::all()]);
    }

    public function test_method_index()
    {
        $stub = $this->getMockForAbstractClass(BaseService::class);
        $this->assertInstanceOf(Response::class, $stub->index([]));
    }

    public function test_protected_method_before_select()
    {
        $this->testReturnPrivateMethod(BaseService::class, 'beforeSelect');
    }

    public function test_protected_method_select()
    {
        $this->testReturnPrivateMethod(BaseService::class, 'select', [ArchModel::all()->random()->id]);
    }

    public function test_protected_method_after_select()
    {
        $this->testReturnPrivateMethod(BaseService::class, 'afterSelect');
    }

    public function test_protected_method_show_register()
    {
        $this->testReturnPrivateMethod(ArchModel::class, 'showRegister', [ArchModel::all()->random()->id]);
    }

    public function test_method_show()
    {
        $model = ArchModel::all()
                    ->random();
        $stub = $this->getMockForAbstractClass(BaseService::class);
        $this->assertInstanceOf(Response::class, $stub->show([], $model->id));
    }

    public function test_protected_method_before_insert()
    {
        $this->testReturnPrivateMethod(BaseService::class, 'beforeInsert');
    }

    public function test_protected_method_insert()
    {
        $this->testReturnPrivateMethod(Response::class, 'insert');
    }

    public function test_protected_method_after_insert()
    {
        $this->testReturnPrivateMethod(BaseService::class, 'afterInsert');
    }

    public function test_method_store()
    {
        $stub = $this->getMockForAbstractClass(BaseService::class);
        $this->assertInstanceOf(Response::class, $stub->store([]));
    }

    public function test_protected_method_before_modify()
    {
        $this->testReturnPrivateMethod(BaseService::class, 'beforeModify');
    }

    public function test_protected_method_modify()
    {
        $this->testReturnPrivateMethod(Response::class, 'modify', [ArchModel::all()->random()->id]);
    }

    public function test_protected_method_after_modify()
    {
        $this->testReturnPrivateMethod(BaseService::class, 'afterModify');
    }

    public function test_method_update()
    {
        $model = ArchModel::all()
                    ->random();
        $stub = $this->getMockForAbstractClass(BaseService::class);
        $this->assertInstanceOf(Response::class, $stub->update([], $model->id));
    }

    public function test_protected_method_before_delete()
    {
        $this->testReturnPrivateMethod(BaseService::class, 'beforeDelete');
    }

    public function test_protected_method_delete()
    {
        $this->testReturnPrivateMethod(BaseService::class, 'delete', [ArchModel::all()->random()->id]);
    }

    public function test_protected_method_after_delete()
    {
        $this->testReturnPrivateMethod(BaseService::class, 'afterDelete');
    }

    public function test_method_destroy()
    {
        $model = ArchModel::all()
                    ->random();
        $stub = $this->getMockForAbstractClass(BaseService::class);
        $this->assertInstanceOf(Response::class, $stub->destroy([], $model->id));
    }

    public function test_protected_method_exception_treatment()
    {
        $this->testReturnPrivateMethod(Response::class, 'exceptionTreatment', [new Exception()]);
    }
}
