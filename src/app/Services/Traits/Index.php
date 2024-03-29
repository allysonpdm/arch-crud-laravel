<?php

namespace ArchCrudLaravel\App\Services\Traits;

use ArchCrudLaravel\App\Enums\Http\StatusCode;
use Exception;
use Illuminate\Database\Eloquent\{
    Builder,
    Model,
    ModelNotFoundException
};
use Illuminate\Http\Response;
use Illuminate\Pagination\{
    LengthAwarePaginator,
    Paginator
};
use Illuminate\Support\Collection;

trait Index
{
    protected ?string $nameModel;
    protected ?string $nameResource;
    protected ?string $nameCollection;
    protected mixed $model;
    protected array $request;
    protected array $relationships = [];

    use TransactionControl, ExceptionTreatment, CacheControl;

    public function index(array $request): Response
    {
        $this->request = $request;
        $perPage = $request['perPage'] ?? 15;
        $page = $request['page'] ?? 1;
        try {

            $cacheKey = $this->createCacheKey(request: $this->request);
            $response = $this->getCache(key: $cacheKey);
            if (!empty($response)) {
                return response($response, StatusCode::OK->value);
            }

            $response = $this->transaction()
                ->beforeList()
                ->list()
                ->afterList()
                ->commit()
                ->model;
            $collection = method_exists($response, 'get') ? $response->get() : $response;
            $response = empty($this->nameCollection)
                ? $response
                ->paginate($perPage)
                ->fragment('' . ($request['fragment'] ?? null))
                : $this->paginate($this->nameCollection::collection($collection), $perPage, $page);

            $this->putCache(
                key: $cacheKey,
                value: $response
            );

            return response($response, StatusCode::OK->value);
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
                $this->model = match($where['condition']){
                    'IS NULL' => $this->model->whereNull($where['column']),
                    'IS NOT NULL' => $this->model->whereNotNull($where['column']),
                    default => $this->model->where($where['column'], $where['condition'], $where['search'])
                };
            }
        }

        if (!empty($orWheres)) {
            foreach ($orWheres as $orWhere) {
                $this->model = match($orWhere['condition']){
                    'IS NULL' => $this->model->orWhereNull($orWhere['column']),
                    'IS NOT NULL' => $this->model->orWhereNotNull($orWhere['column']),
                    default => $this->model->orWhere($orWhere['column'], $orWhere['condition'], $orWhere['search'])
                };
            }
        }

        $this->model = $this->model
            ->with($this->relationships);

        if (!$this->model->exists()) {
            throw new ModelNotFoundException;
        }

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
}

