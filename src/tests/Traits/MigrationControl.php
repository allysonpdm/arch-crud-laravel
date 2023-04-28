<?php

namespace ArchCrudLaravel\Tests\Traits;

trait MigrationControl
{
    protected function runMigration(string $provider): void
    {
        // Publica a migration
        $this->artisan('vendor:publish', [
            '--provider' => $provider,
            '--tag' => 'migrations'
        ]);

        // Executa a migration
        $this->artisan('migrate');
    }
    
    protected function rollbackMigrations(): void
    {
        // Remove a tabela de testes
        $migrator = app('migrator');
        $migrator->rollback([database_path('migrations')]);
    }
}
