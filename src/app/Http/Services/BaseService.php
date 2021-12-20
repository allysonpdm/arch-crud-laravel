<?php

namespace App\Http\Services;

use App\Exceptions\BusinessException;
use App\Exceptions\CreateException;
use App\Exceptions\SoftDeleteException;
use App\Rules\FieldsExistsInTableRule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use ReflectionClass;
use stdClass;

class BaseService
{

    private $id;
    private $data;
    private $dataSheet;
    private $params;
    private $register;
    private $now;

    protected $model;
    protected object $business;

    public $request;

    private const KEY_OPERATOR = 0;
    private const KEY_VALUE = 1;


    public function __construct()
    {
        $this->business = new stdClass();
        $this->now = date('Y-m-d H:i:s');
    }

    public function setPrimaryModel($model)
    {
        $this->model = bootUp($model);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $validated = self::validateParameters(
            $this->request->input(),
            [
                'orderBy' => [
                    'bail',
                    'array',
                    new FieldsExistsInTableRule($this->model->getTable())
                ],
                'page'=> 'bail|integer',
                'perPage' => 'bail|integer',
                'search' => [
                    'bail',
                    new FieldsExistsInTableRule($this->model->getTable())
                ]
            ]
        );
        return $this->prepareFilterParameters($validated)
                    ->beforeList()
                    ->validateInternalRules()
                    ->list()
                    ->afterList();
    }

    protected static function validateParameters($inputs, $rules, $messages = [])
    {
        $validator = Validator::make(
            $inputs,
            $rules,
            $messages
        );

        if(!empty($validator) && $validator->fails())
            throw new ValidationException($validator->errors());

        return $validator->validated();
    }

    protected function prepareFilterParameters($validated)
    {
        $this->params = new stdClass;
        $this->params->orderBy = $validated['orderBy'] ?? [];
        $this->params->perPage = $validated['perPage'] ?? null;
        $this->params->search = $validated['search'] ?? [];
        return $this;
    }

    protected function beforeList()
    {
        return $this;
    }

    protected function validateInternalRules()
    {
        if(isset($this->business->params) && isset($this->business->rules)){
            $validator = Validator::make(
                $this->business->params,
                $this->business->rules,
                $this->business->messages ?? []
            );

            if ($validator->fails())
                throw new BusinessException($validator->errors());
        }

        return $this;
    }

    protected function list()
    {
        $this->dataSheet = $this->applyFilters();

        foreach ($this->params->orderBy as $key => $value)
            $this->dataSheet = $this->dataSheet->orderBy($key, $value);

        $this->register = $this->dataSheet->paginate($this->params->perPage);

        return $this;
    }

    protected function applyFilters(): Builder
    {
        return $this->mandatoryConditions()
                    ->userConditions()
                    ->model;
    }

    protected function mandatoryConditions()
    {
        foreach($this->model->queryFilters as $field => $search){
            switch($field){
                case 'OR':
                    $this->model = $this->model->orWhere($search);
                    break;
                default:
                    $this->model = $this->model->where($search);
            }
        }

        return $this;
    }

    protected function userConditions()
    {
        foreach($this->params->search as $field => $search){
            $this->model = $this->searchTreatment($field, $search);
        }

        return $this;
    }

    private function searchTreatment($field, $search)
    {
        return is_array($search)
            ? $this->model->where($field, $search[self::KEY_OPERATOR],  $search[self::KEY_VALUE])
            : $this->model->where($field, $search);
    }

    protected function afterList()
    {
        return $this->register;
    }

    /**
     * Show the form for creating a new resource.
     * @param null|array $request
     * @return \Illuminate\Http\Response
     */
    public function create(?array $request)
    {
        return $this->beforeInsert($request)
                    ->validateInternalRules()
                    ->insert()
                    ->afterInsert();
    }

    protected function beforeInsert(array $request): object
    {
        $this->data = array_merge($request, self::prepareInsert());
        return $this;
    }

    protected static function prepareInsert(): array
    {
        return [];
    }

    protected function insert(): object
    {
        $this->register = $this->model->create($this->data);
        if(!$this->register->id)
            throw new CreateException;

        return $this;
    }

    protected function afterInsert(): object
    {
        return $this->register;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $this->id = $id;
        return $this->beforeSelect()
                    ->validateInternalRules()
                    ->select()
                    ->afterSelect();
    }

    protected function beforeSelect()
    {
        return $this;
    }

    protected function select()
    {
       $this->data = $this->model->findOrFail($this->id);
       return $this;
    }

    protected function afterSelect()
    {
        return $this->data;
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
     * @param  array  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(array $request, $id)
    {
        $this->id = $id;
        return $this->beforeModify($request)
                    ->validateInternalRules()
                    ->modify()
                    ->afterModify();
    }

    protected function beforeModify(array $request)
    {
        $this->data = array_merge($request, self::prepareModify());
        return $this;
    }

    protected static function prepareModify(): array
    {
        return [];
    }

    protected function modify()
    {
        $this->register = $this->model->findOrFail($this->id);
        $this->register->update($this->data);
        return $this;
    }

    protected function afterModify()
    {
        return $this->register;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $this->id = $id;

        return $this->beforeDelete()
                    ->validateInternalRules()
                    ->delete()
                    ->afterDelete()
                    ->register;
    }

    protected static function getRelationships($model)
    {
        $typesOfRelationships = [
            HasOne::class,
            HasMany::class,
            BelongsTo::class,
            BelongsToMany::class,
            MorphTo::class,
            MorphToMany::class
        ];
        $reflector = new ReflectionClass($model);
        $relations = [];
        foreach ($reflector->getMethods() as $reflectionMethod) {
            $returnType = $reflectionMethod->getReturnType();
            if ($returnType) {
                if (in_array($returnType->getName(), $typesOfRelationships))
                    $relations[] = $reflectionMethod;
            }
        }
        return $relations;
    }

    protected function hasRelationships()
    {
        $relations = self::getRelationships($this->model);
        $this->register =  $this->model->findOrFail($this->id);

        foreach($relations as $relation){
            if(!empty($this->register->{$relation->name}))
                return true;
        }
        return false;
    }

    protected function beforeDelete()
    {
        return $this;
    }

    protected function delete()
    {
        if($this->hasRelationships()){
            self::softDelete($this->register, $this->model::DELETED_AT, $this->now);
            throw new SoftDeleteException;
        }else{
            $this->register->delete();
        }

        return $this;
    }

    protected static function softDelete($register, $nameColumn, $value)
    {
        $register->update([$nameColumn => $value]);
    }

    private function afterDelete()
    {
        return $this;
    }
}
