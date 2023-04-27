<?php

namespace ArchCrudLaravel\App\Console\Commands;

use Illuminate\Console\Command;

class AddMigrationToProvider extends Command
{
    protected $signature = 'add:migration {name : Nome da migration}';
    protected $description = 'Adicionar uma migration ao ServiceProvider';

    public function handle()
    {
        $name = $this->argument('name');
        $path = $this->createMigration($name);
        $this->addToProvider($path);
        $this->info('Migration criada e adicionada ao Provider com sucesso!');
    }

    protected function createMigration($name)
    {
        $timestamp = now()->format('Y_m_d_His');
        $path = database_path('migrations/'.$timestamp.'_'.$name.'.php');
        $stub = file_get_contents(__DIR__.'/stubs/migration.stub');
        $stub = str_replace('{{name}}', $name, $stub);
        file_put_contents($path, $stub);
        return $path;
    }

    protected function addToProvider($path)
    {
        $provider = app_path('Providers/MyPackageServiceProvider.php');
        $content = file_get_contents($provider);
        $newContent = str_replace(
            'public function boot()',
            "public function boot()\n\t\t{\n\t\t\t\$this->loadMigrationsFrom('{$path}');\n\t\t}",
            $content
        );
        file_put_contents($provider, $newContent);
    }
}
