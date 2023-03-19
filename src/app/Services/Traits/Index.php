<?php

namespace ArchCrudLaravel\App\Services\Traits;

use Exception;
use Illuminate\Http\Response;
use Illuminate\Pagination\{
    LengthAwarePaginator,
    Paginator
};
use Illuminate\Support\Collection;

trait Index
{
    protected $nameResource;
    protected $nameCollection;
    protected $model;
    protected $request;
    protected $relationships = [];

    use TransactionControl, ExceptionTreatment;

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
}
