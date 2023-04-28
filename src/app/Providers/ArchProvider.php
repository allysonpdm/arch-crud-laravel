<?php

namespace ArchCrudLaravel\App\Providers;

use Illuminate\Support\ServiceProvider;

class ArchProvider extends ServiceProvider
{
    public function boot()
    {
        // Publica a migration
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../../database/migrations' => database_path('migrations'),
            ], 'migrations');

            $this->publishes([
                __DIR__.'/../../database/seeders' => database_path('seeders'),
            ], 'seeders');
        }
    }

    public function register()
    {
        // Registra outros serviços, se necessário
    }
}
