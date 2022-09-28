<?php

namespace ArchCrudLaravel\App\Services;

use ArchCrudLaravel\App\Exceptions\{
    BusinessException,
    CreateException,
    SoftDeleteException,
    UpdateException
};
use ArchCrudLaravel\App\Models\BaseModel;
use Exception;
use Illuminate\Database\Eloquent\{
    Model,
    ModelNotFoundException
};
use Illuminate\Database\QueryException;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use ReflectionClass;

abstract class BaseService implements TemplateService
{
    protected $nameModel;
    protected $nameResource;
    protected $nameCollection;
    protected $model;
    protected $request;
    protected $relationships = [];
    protected bool $onTransaction = true;

    public function __construct()
    {
        $this->model = new ($this->nameModel ?? BaseModel::class);
        $this->now = date('Y-m-d H:i:s');
    }

    protected function transaction()
    {
        if ($this->onTransaction) {
            DB::beginTransaction();
        }
        return $this;
    }

    protected function commit()
    {
        if ($this->onTransaction) {
            DB::commit();
        }
        return $this;
    }

    protected function rollBack()
    {
        if ($this->onTransaction) {
            DB::rollBack();
        }
        return $this;
    }

    public function index(array $request): Response
    {
        $this->request = $request;
        $perPage = $request['perPage'] ?? 15;
        $page = $request['page'] ?? 1;
        try {
            $response = $this->transaction()
                ->beforeList()
                ->list()
                ->afterList()
                ->commit()
                ->model;

            $response = empty($this->nameCollection)
                ? $response
                ->paginate($perPage)
                ->fragment('' . ($request['fragment'] ?? null))
                : $this->paginate($this->nameCollection::collection($response->get()), $perPage, $page);

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

    protected function ordenation(?array $orderBy)
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

    public function paginate($items, $perPage = 15, $page = 1, $options = [])
    {
        $page = $page ?? Paginator::resolveCurrentPage();
        $items = $items instanceof Collection ? $items : Collection::make($items);
        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
    }

    public function show(array $request, string|int $id): Response
    {
        try {
            $response = $this->transaction()
                ->beforeSelect()
                ->select($id)
                ->afterSelect()
                ->commit()
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

    protected function showRegister($id = null)
    {
        if (empty($id)) {
            $id = $this->model->id ?? $this->model::where($this->request);
        }

        $register = $this->model::with($this->relationships)->findOrFail($id);

        return empty($this->nameResource)
            ? $register
            : new $this->nameResource($register);
    }

    public function store(array $request): Response
    {
        $this->request = $request;
        try {
            $response = $this->transaction()
                ->beforeInsert()
                ->insert()
                ->afterInsert()
                ->commit()
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

    public function update(array $request, string|int $id): Response
    {
        $this->request = $request;
        try {
            $response = $this->transaction()
                ->beforeModify()
                ->modify($id)
                ->afterModify()
                ->commit()
                ->showRegister($request['id'] ?? $id);
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
        try {
            if (empty($this->request)) {
                throw new UpdateException;
            }
            $this->model = $this->model->findOrFail($id);
            $this->model->update($this->request);
            return $this;
        } catch (Exception $exception) {
            return $this->exceptionTreatment($exception);
        }
    }

    protected function afterModify()
    {
        return $this;
    }

    public function destroy(array $request, string|int $id): Response
    {
        $this->request = $request;
        try {
            $response = $this->transaction()
                ->beforeDelete()
                ->delete($id)
                ->afterDelete()
                ->commit()
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
        $force = $this->request['force'] ?? false;
        $register = $this->model->findOrFail($id);
        if (!self::isActive($register, $this->model::DELETED_AT)) {
            throw new SoftDeleteException;
        }
        $this->model = self::hasRelationships($register)
            ? $this->softOrHardDelete($force, $register)
            : $register->delete();
        return $this;
    }

    protected static function hasRelationships(Model $register): bool
    {
        $has = false;
        $relations = self::getRelationships($register);

        foreach ($relations as $relation) {
            if (!empty($register->{$relation}) && $register->{$relation}->count() > 0) {
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
            if ($returnType && (in_array(class_basename($returnType->getName()), $typesOfRelationships))) {
                $relations[] = $reflectionMethod->name;
            }
        }

        return $relations;
    }

    protected function softOrHardDelete($force, $register)
    {
        if ($force) {
            return self::hardDelete($register);
        }

        return self::softDelete($register, $this->model::DELETED_AT, $this->now);
    }

    protected static function hardDelete($register)
    {
        $relations = self::getRelationships($register);
        foreach ($relations as $relationName) {
            if (!empty($register->{$relationName}) && $register->{$relationName}->count() > 0) {
                $relation = $register->{$relationName}();
                if (method_exists($relation, 'dissociate')) {
                    $relation->dissociate();
                }
                if (method_exists($relation, 'detach')) {
                    $relation->detach();
                }
            }
        }
        return $register->delete();
    }

    protected static function softDelete(Model $register, string $nameColumn, string $value): bool
    {
        return $register->update([$nameColumn => $value]);
    }

    protected static function isActive(Model $register, string $nameColumn): bool
    {
        return empty($register->{$nameColumn});
    }

    protected function afterDelete()
    {
        return $this;
    }

    protected function exceptionTreatment($exception): Response
    {
        $this->rollBack();
        $type = get_class($exception);
        $code = (int) $exception->getCode();
        $code = empty($code) ? 500 : $code;

        switch ($type) {
            case ValidationException::class:
                $response = response($exception->validator->messages(), 422); // HTTP error 422
                break;
            case ModelNotFoundException::class:
                $response = response("{ message: \"" . __('exceptions.error.no_results') . "\"}", 404);
                break;
            case CreateException::class:
            case BusinessException::class:
            case UpdateException::class:
                $response = response($exception->getMessage(), $code);
                break;
            case SoftDeleteException::class:
                $response = response($exception->getMessage(), 200);
                break;
            case QueryException::class:
                $response = response($exception->getMessage(), 500);
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