<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\QueueService;

class QueueServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(QueueService::class, function ($app) {
            return new QueueService();
        });
    }

    public function boot(): void
    {
        //
    }
}