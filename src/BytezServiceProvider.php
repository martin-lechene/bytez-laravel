<?php

namespace Bytez\LaravelBytez;

use Bytez\LaravelBytez\Services\BytezClient;
use Bytez\LaravelBytez\Services\BytezManager;
use Illuminate\Support\ServiceProvider;

class BytezServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/bytez.php',
            'bytez'
        );

        $this->app->singleton(BytezClient::class, function ($app) {
            return new BytezClient(
                config('bytez.api_key'),
                config('bytez.base_url', 'https://api.bytez.com'),
                config('bytez.timeout', 30),
                config('bytez.retry_attempts', 3),
                config('bytez.retry_delay', 1000),
                $app['cache']
            );
        });

        $this->app->singleton(BytezManager::class, function ($app) {
            return new BytezManager($app->make(BytezClient::class));
        });

        $this->app->alias(BytezClient::class, 'bytez.client');
        $this->app->alias(BytezManager::class, 'bytez');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/bytez.php' => config_path('bytez.php'),
        ], 'bytez-config');
    }
}
