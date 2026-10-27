<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->extend(\Illuminate\Foundation\Console\ServeCommand::class,
            fn ($command, $app) => $app->make(\App\Console\Commands\ServeWithAiWorkerCommand::class));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
