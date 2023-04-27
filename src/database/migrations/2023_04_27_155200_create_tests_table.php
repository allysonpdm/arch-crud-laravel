<?php

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class AddMigrationToProvider extends Command
{
    protected $signature = 'migration:add {migration}';
    
    protected $description = 'Adiciona uma migration ao Provider.';

    public function handle()
    {
        $migration = $this->argument('migration');
        $path = database_path('migrations/'.$migration);

        if (! File::exists($path)) {
            $this->error('Migration '.$migration.' não existe.');
            return;
        }

        $provider = app()->getProvider(MyPackageServiceProvider::class);
        $migrations = $provider->migrations;

        if (in_array($migration, $migrations)) {
            $this->info('Migration '.$migration.' já foi adicionada ao Provider.');
            return;
        }

        $migrations[] = $migration;
        $provider->migrations = $migrations;
        $provider->register();
        $this->info('Migration '.$migration.' adicionada ao Provider.');
    }
}
