<?php

namespace App\Http\Services;

use App\Exceptions\BusinessException;
use App\Exceptions\CreateException;
use App\Exceptions\SoftDeleteException;
use App\Rules\FieldsExistsInTableRule;
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

    protected $model;
    protected object $business;

    public $request;

    const INDEX_OF_COLUMN = 0;
    const INDEX_OF_CONDITION = 1;
    const INDEX_OF_SEARCH = 2;

    public function __construct()
    {
        $this->business = new stdClass();
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
                'search' => 'bail|string'
            ]
        );
        return $this->filterParameters($validated)
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

    protected function filterParameters($validated)
    {
        $this->params = new stdClass;
        $this->params->orderBy = $validated['orderBy'] ?? [];
        $this->params->perPage = $validated['perPage'] ?? null;
        $this->params->search = !empty($validated['search']) ? "%$validated[search]%" : null;
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

    protected function applyFilters()
    {
        $filter = $this->model->queryFilters;
        if (!empty($filter)) {
            $this->setConditions($filter);
        }
        return $this->model;
    }

    private function setConditions(array $filter): void
    {
        foreach ($filter as $key => $array) {
            if($key === 'OR'){
                foreach ($array as $k => $arr)
                    $this->model = $this->or($arr);
            }elseif($key === 'AND'){
                foreach ($array as $k => $arr)
                    $this->model = $this->and($arr);
            }else{
                $this->model = $this->and($array);
            }
        }
    }

    private function or($arr){
        return sizeof($arr) > 2
            ? $this->model->orWhere($arr[self::INDEX_OF_COLUMN], $arr[self::INDEX_OF_CONDITION], $this->search($arr))
            : $this->model->orWhere($this->whereParameterTreatment($arr));
    }

    private function and($arr){
        return sizeof($arr) > 2
            ? $this->model->where($arr[self::INDEX_OF_COLUMN], $arr[self::INDEX_OF_CONDITION], $this->search($arr))
            : $this->model->where($this->whereParameterTreatment($arr));
    }

    private function search($arr)
    {
        return $arr[self::INDEX_OF_SEARCH] ?? $this->params->search ?? null;
    }

    private function whereParameterTreatment($array)
    {
        if(array_key_exists(0, $array))
            return [$array[self::INDEX_OF_COLUMN],  $this->search($array)];

        return $array;
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
                    ->afterDelete();
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
                if (in_array(class_basename($returnType->getName()), $typesOfRelationships))
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
            self::softDelete($this->register);
            throw new SoftDeleteException;
        }else{
            $this->register->delete();
        }

        return $this;
    }

    protected static function softDelete($register)
    {
        $register->update(['deleted_at' => date('Y-m-d H:i:s')]);
    }

    private function afterDelete()
    {
        return $this->register;
    }
}
