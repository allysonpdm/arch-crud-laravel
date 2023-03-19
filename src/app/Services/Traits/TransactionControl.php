<?php

namespace ArchCrudLaravel\App\Services\Traits;

use Illuminate\Support\Facades\DB;

trait TransactionControl
{
    protected bool $onTransaction = true;

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
}
