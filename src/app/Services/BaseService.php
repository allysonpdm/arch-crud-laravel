<?php

namespace ArchCrudLaravel\App\Services;

use ArchCrudLaravel\App\Models\BaseModel;
use ArchCrudLaravel\App\Services\Contracts\TemplateService;
use ArchCrudLaravel\App\Services\Traits\{
    Destroy,
    Index,
    Show,
    Store,
    Update
};
use Illuminate\Database\Eloquent\{
    Builder,
    Model
};

abstract class BaseService implements TemplateService
{
    protected ?string $nameModel;
    protected $nameResource;
    protected $nameCollection;
    protected Model|Builder|null $model;
    protected array $request;
    protected array $relationships = [];
    protected bool $onTransaction = true;
    protected bool $onCache = true;
    protected string $now;

    use Store, Index, Show, Update, Destroy;

    public function __construct()
    {
        $this->model = new ($this->nameModel ?? BaseModel::class);
        $this->now = date('Y-m-d H:i:s');
    }
}
