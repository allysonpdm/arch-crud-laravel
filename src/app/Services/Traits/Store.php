<?php

namespace ArchCrudLaravel\App\Services\Traits;

use ArchCrudLaravel\App\Enums\Http\StatusCode;
use ArchCrudLaravel\App\Exceptions\CreateException;
use Exception;
use Illuminate\Database\Eloquent\{
    Builder,
    Model
};
use Illuminate\Http\Response;

trait Store
{
    protected ?string $nameResource;
    protected mixed $model;
    protected array $request;

    use TransactionControl, ExceptionTreatment, ShowRegister;

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
            return response($response, StatusCode::CREATED->value);
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
        if (empty($this->request)) {
            throw new CreateException;
        }
        $this->model = $this->model::create($this->request);

        return $this;
    }

    protected function afterInsert()
    {
        return $this;
    }
}
