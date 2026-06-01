<?php

declare(strict_types=1);

namespace App\Providers;

use App\Interfaces\Repositories\CityRepositoryInterface;
use App\Interfaces\Repositories\DashboardRepositoryInterface;
use App\Interfaces\Repositories\TravelRequestRepositoryInterface;
use App\Interfaces\Repositories\UserRepositoryInterface;
use App\Interfaces\Services\DashboardServiceInterface;
use App\Interfaces\Services\IbgeServiceInterface;
use App\Interfaces\Services\TravelRequestServiceInterface;
use App\Interfaces\Services\UserServiceInterface;
use App\Repositories\CityRepository;
use App\Repositories\DashboardRepository;
use App\Repositories\TravelRequestRepository;
use App\Repositories\UserRepository;
use App\Services\DashboardService;
use App\Services\IbgeService;
use App\Services\TravelRequestService;
use App\Services\UserService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(IbgeServiceInterface::class, IbgeService::class);

        $this->app->singleton(
            CityRepositoryInterface::class,
            fn ($app) => new CityRepository($app->make(IbgeServiceInterface::class))
        );

        $this->app->bind(TravelRequestRepositoryInterface::class, TravelRequestRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(DashboardRepositoryInterface::class, DashboardRepository::class);

        $this->app->bind(TravelRequestServiceInterface::class, TravelRequestService::class);
        $this->app->bind(UserServiceInterface::class, UserService::class);
        $this->app->bind(DashboardServiceInterface::class, DashboardService::class);
    }

    public function boot(): void {}
}
