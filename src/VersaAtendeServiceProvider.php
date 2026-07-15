<?php

namespace Versa\VersaAtende;

use Illuminate\Support\ServiceProvider;
use Versa\VersaAtende\VersaAtende;

class VersaAtendeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/versa-atende.php',
            'versa-atende'
        );

        $this->app->singleton('versa-atende', function() {
            return new VersaAtende(
                config('versa-atende.base_url'),
                config('versa-atende.x_admin_key')
            );
        });
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/versa-atende.php' => config_path('versa-atende.php'),
            ], 'versa-atende-config');
        }
    }

    protected function registerMigrations(): void
    {
        $stubPath = __DIR__ . '/../database/migrations';
        $targetPath = database_path('migrations');

        $migrations = glob($stubPath . '/*.stub');

        $publishArray = [];
        $delay = 0;

        foreach ($migrations as $stub) {
            $filename = basename($stub, '.php.stub');
            $filename = basename($filename, '.stub');

            $newPath = $targetPath . '/' . date('Y_m_d_His', time() + $delay) . '_' . $filename . '.php';

            $publishArray[$stub] = $newPath;
            $delay++;
        }

        $this->publishes($publishArray, 'versa-atende-migrations');
    }
}