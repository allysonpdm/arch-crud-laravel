<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Request as Input;
use Illuminate\Routing\Controller;

class BaseController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $name;
    protected $service;
    private $request;

    public function __construct()
    {
        $this->name = self::getControllerName($this);
        $this->request = app("App\\Http\\Requests\\{$this->name}Request");
        $this->service = bootUp(["App\\Http\\Services\\{$this->name}Service"]);
        $this->service->setPrimaryModel(["App\\Models\\{$this->name}Model"]);
        $this->service->request = &$this->request;
        $this->setParamsOnRequest();
    }

    private static function getControllerName($obj)
    {
        $obj->name = get_class($obj);
        return $obj->removePrefixName()->removeSuffixName()->name;
    }

    private function removePrefixName()
    {
        $this->name = str_replace('App\\Http\\Controllers\\', '',  $this->name);
        return $this;
    }

    private function removeSuffixName()
    {
        $this->name = str_replace('Controller', '',  $this->name);
        return $this;
    }

    private function setParamsOnRequest()
    {
        $this->request->merge(self::getUrlParams());
    }

    private static function getUrlParams()
    {
        return Input::all();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try{
            return $this->service->index();
        }catch(Exception $exception){
            return $this->service->exceptionTreatment($exception);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try{
            return $this->service->create($request->validated());
        }catch(Exception $exception){
            return $this->service->exceptionTreatment($exception);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try{
            return $this->service->show($id);
        }catch(Exception $exception){
            return $this->service->exceptionTreatment($exception);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try{
            return $this->service->update($request->all(), $id);
        }catch(Exception $exception){
            return $this->service->exceptionTreatment($exception);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try{
            return $this->service->destroy($id);
        }catch(Exception $exception){
            return $this->service->exceptionTreatment($exception);
        }
    }    
}
