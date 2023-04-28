<?php

namespace ArchCrudLaravel\Tests\Traits;

use Illuminate\Support\ServiceProvider;

trait MigrationControl
{
    protected function executeMigration(ServiceProvider $provider)
    {
        // Publica a migration
        $this->artisan('vendor:publish', [
            '--provider' => $provider,
            '--tag' => 'migrations'
        ]);

        // Executa a migration
        $this->artisan('migrate');
    }
    
    protected function tearDown(): void
    {
        // Remove a tabela de testes
        $migrator = app('migrator');
        $migrator->rollback([database_path('migrations')]);

        parent::tearDown();
    }
}
