<?php

namespace ArchCrudLaravel\Tests\Unit\Arch\App\Services\Traits;

use ArchCrudLaravel\App\Services\Traits\TransactionControl;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TransactionControlTest extends TestCase
{
    use TransactionControl;

    protected function setUp(): void
    {
        parent::setUp();

        $this->onTransaction = true;
    }

    public function testTransaction()
    {
        $this->transaction();

        $this->assertTrue(DB::transactionLevel() > 0);
    }

    public function testCommit()
    {
        $this->transaction();
        $this->commit();

        $this->assertEquals(0, DB::transactionLevel());
    }

    public function testRollBack()
    {
        $this->transaction();
        $this->rollBack();

        $this->assertEquals(0, DB::transactionLevel());
    }

    public function testTransactionDisabled()
    {
        $this->onTransaction = false;

        $this->transaction();
        $this->commit();

        $this->assertEquals(0, DB::transactionLevel());
    }
}
