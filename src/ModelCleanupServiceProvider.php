<?php

namespace Spatie\ModelCleanup;

use Illuminate\Support\ServiceProvider;
use Spatie\ModelCleanup\CleanupConfig\CleanupConfigFactory;
use Spatie\ModelCleanup\Commands\CleanUpModelsCommand;

class ModelCleanupServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/model-cleanup.php' => config_path('model-cleanup.php'),
        ], 'config');
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/model-cleanup.php', 'model-cleanup');

        $this->app->bind(CleanupConfigFactory::class, function () {
            return app(config('model-cleanup.default_cleanup_config'));
        });

        $this->commands([
            CleanUpModelsCommand::class,
        ]);
    }
}
