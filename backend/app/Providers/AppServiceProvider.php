<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\IbgeService;
use App\Repositories\CityRepository;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(IbgeService::class, function ($app) {
            return new IbgeService();
        });

        $this->app->singleton(CityRepository::class, function ($app) {
            return new CityRepository($app->make(IbgeService::class));
        });
    }
    public function boot(): void
    {
        //
    }
}
