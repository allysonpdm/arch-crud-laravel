<?php

use ArchCrudLaravel\App\Services\Traits\TransactionControl;
use Illuminate\Support\Facades\DB;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Tests\TestCase;

class TransactionControlTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testTransaction()
    {
        // create mock object for DB facade
        $dbMock = Mockery::mock(DB::class);
        $dbMock->shouldReceive('beginTransaction')->once();

        // replace actual DB facade with the mock
        $this->app->instance('db', $dbMock);

        // instantiate the class that uses the trait
        $class = new class {
            use TransactionControl;
        };

        // call the transaction method and assert that the mock was called
        $this->assertSame($class, $class->transaction());
        $dbMock->shouldHaveReceived('beginTransaction')->once();
    }

    public function testCommit()
    {
        // create mock object for DB facade
        $dbMock = Mockery::mock(DB::class);
        $dbMock->shouldReceive('commit')->once();

        // replace actual DB facade with the mock
        $this->app->instance('db', $dbMock);

        // instantiate the class that uses the trait
        $class = new class {
            use TransactionControl;
        };

        // call the commit method and assert that the mock was called
        $this->assertSame($class, $class->commit());
        $dbMock->shouldHaveReceived('commit')->once();
    }

    public function testRollback()
    {
        // create mock object for DB facade
        $dbMock = Mockery::mock(DB::class);
        $dbMock->shouldReceive('rollBack')->once();

        // replace actual DB facade with the mock
        $this->app->instance('db', $dbMock);

        // instantiate the class that uses the trait
        $class = new class {
            use TransactionControl;
        };

        // call the rollback method and assert that the mock was called
        $this->assertSame($class, $class->rollBack());
        $dbMock->shouldHaveReceived('rollBack')->once();
    }
}
