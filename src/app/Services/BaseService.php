<?php

namespace App\Services;

use App\Exceptions\{
    BusinessException,
    CreateException,
    SoftDeleteException,
    UpdateException
};
use Exception;
use Illuminate\Database\Eloquent\{
    Model,
    ModelNotFoundException
};
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use ReflectionClass;

abstract class BaseService implements TemplateService
{

    protected $nameModel;
    protected $model;
    protected $request;
    protected $relationships = [];

    public function __construct()
    {
        $this->model = new ($this->nameModel);
        $this->now = date('Y-m-d H:i:s');
    }

    public function index(array $request): Response
    {
        $this->request = $request;
        try {
            $response = $this->beforeList()
                ->list()
                ->afterList()
                ->model
                ->paginate($request['perPage'] ?? null)
                ->fragment('' . ($request['fragment'] ?? null));
            return response($response, 200);
        } catch (Exception $exception) {
            return $this->exceptionTreatment($exception);
        }
    }

    protected function beforeList()
    {
        return $this;
    }

    protected function list()
    {
        $wheres = $this->request['wheres'] ?? null;
        $orWheres = $this->request['orWheres'] ?? null;
        $ordenation = $this->request['orderBy'] ?? null;

        $this->search($wheres, $orWheres)
            ->ordenation($ordenation);

        return $this;
    }

    private function search(?array $wheres, ?array $orWheres)
    {
        if (!empty($wheres)) {
            foreach ($wheres as $where) {
                $this->model = $this->model->where($where['column'], $where['condition'], $where['search']);
            }
        }

        if (!empty($orWheres)) {
            foreach ($orWheres as $orWhere) {
                $this->model = $this->model->orWhere($orWhere['column'], $orWhere['condition'], $orWhere['search']);
            }
        }

        $this->model = $this->model
            ->with($this->relationships);

        return $this;
    }

    private function ordenation(?array $orderBy)
    {
        if (!empty($orderBy)) {
            foreach ($orderBy as $column => $order) {
                $this->model = $this->model->orderBy($column, $order);
            }
        }

        return $this;
    }

    protected function afterList()
    {
        return $this;
    }

    public function show(array $request, string|int $id): Response
    {
        try {
            $response = $this->beforeSelect()
                ->select($id)
                ->afterSelect()
                ->showRegister($id);
            return response($response, 200);
        } catch (Exception $exception) {
            return $this->exceptionTreatment($exception);
        }
    }

    protected function beforeSelect()
    {
        return $this;
    }

    protected function select(string|int $id)
    {
        $this->model = $this->model::findOrFail($id);
        return $this;
    }

    protected function afterSelect()
    {
        return $this;
    }

    public function store(array $request): Response
    {
        $this->request = $request;
        try {
            $response = $this->beforeInsert()
                ->insert()
                ->afterInsert()
                ->showRegister();
            return response($response, 201);
        } catch (Exception $exception) {
            return $this->exceptionTreatment($exception);
        }
    }

    protected function beforeInsert()
    {
        return $this;
    }

    protected function insert()
    {
        try {
            if (empty($this->request)) {
                throw new CreateException;
            }
            $this->model = $this->model::create($this->request);
            return $this;
        } catch (Exception $exception) {
            return $this->exceptionTreatment($exception);
        }
    }

    protected function afterInsert()
    {
        return $this;
    }

    protected function showRegister($id = null)
    {
        if (empty($id)) {
            $id = $this->model->id;
        }
        return $this->model::with($this->relationships)
            ->findOrFail($id);
    }

    public function update(array $request, string|int $id): Response
    {
        $this->request = $request;
        try {
            $response = $this->beforeModify()
                ->modify($id)
                ->afterModify()
                ->showRegister($id);
            return response($response, 200);
        } catch (Exception $exception) {
            return $this->exceptionTreatment($exception);
        }
    }

    protected function beforeModify()
    {
        return $this;
    }

    protected function modify(string|int $id)
    {
        if (empty($this->request)) {
            throw new UpdateException;
        }
        $this->model = $this->model->findOrFail($id);
        $this->model->update($this->request);
        return $this;
    }

    protected function afterModify()
    {
        return $this;
    }

    public function destroy(array $request, string|int $id): Response
    {
        try {
            $response = $this->beforeDelete()
                ->delete($id)
                ->afterDelete()
                ->model;
            return response($response, 200);
        } catch (Exception $exception) {
            return $this->exceptionTreatment($exception);
        }
    }

    protected function beforeDelete()
    {
        return $this;
    }

    protected function delete(string|int $id)
    {
        $register = $this->model->findOrFail($id);
        if(!self::isActive($register, $this->model::DELETED_AT)){
            throw new SoftDeleteException;
        }
        $this->model = self::hasRelationships($this->model, $register)
            ? self::softDelete($register, $this->model::DELETED_AT, $this->now)
            : $register->delete();
        return $this;
    }

    protected static function hasRelationships(Model $model, Model $register): bool
    {
        $has = false;
        $relations = self::getRelationships($model);

        foreach ($relations as $relation) {
            if (!empty($register->{$relation}) && $register->{$relation}->count() > 0 ){
                $has = true;
            }
        }
        return $has;
    }

    protected static function getRelationships(Model $model): array
    {
        $typesOfRelationships = [
            'HasOne',
            'HasMany',
            'BelongsTo',
            'BelongsToMany',
            'MorphTo',
            'MorphToMany'
        ];
        $reflector = new ReflectionClass($model);
        $relations = [];
        foreach ($reflector->getMethods() as $reflectionMethod) {
            $returnType = $reflectionMethod->getReturnType();
            if ($returnType && (in_array(class_basename($returnType->getName()), $typesOfRelationships))){
                $relations[] = $reflectionMethod->name;
            }
        }

        return $relations;
    }

    protected static function softDelete(Model $register, string $nameColumn, string $value): bool
    {
        return $register->update([$nameColumn => $value]);
    }

    protected static function isActive(Model $register, string $nameColumn): bool
    {
        return empty($register->{$nameColumn}) ? true : false;
    }

    protected function afterDelete()
    {
        return $this;
    }

    protected function exceptionTreatment($exception): Response
    {
        $type = get_class($exception);
        $code = (int) $exception->getCode();
        $code = empty($code) ? 500 : $code;

        switch ($type) {
            case ValidationException::class:
                $response = response($exception->validator->messages(), 422); // HTTP error 422
                break;
            case ModelNotFoundException::class:
                $response = response(__('exceptions.error.no_results'), 404);
                break;
            case CreateException::class:
                $response = response($exception->getMessage(), $code);
                break;
            case UpdateException::class:
                $response = response($exception->getMessage(), $code);
                break;
            case SoftDeleteException::class:
                $response = response($exception->getMessage(), 200);
                break;
            case BusinessException::class:
                $response = response($exception->getMessage(), $code);
                break;
            default:
                $response = response([
                    'Exception' => $type,
                    'Message' => $exception->getMessage(),
                    'File' => $exception->getFile(),
                    'Line' => $exception->getLine(),
                ], $code);
        }
        return $response;
    }
}
