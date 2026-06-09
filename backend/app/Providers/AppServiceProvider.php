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
        $this->app->bind(
            \App\Repositories\Contracts\UserRepositoryInterface::class,
            \App\Repositories\Eloquent\EloquentUserRepository::class
        );

        $this->app->bind(
            \App\Repositories\Contracts\EquipmentRepositoryInterface::class,
            \App\Repositories\Eloquent\EloquentEquipmentRepository::class
        );

        $this->app->bind(
            \App\Repositories\Contracts\ReservationRepositoryInterface::class,
            \App\Repositories\Eloquent\EloquentReservationRepository::class
        );

        $this->app->bind(
            \App\Services\Contracts\ReservationServiceInterface::class,
            \App\Services\ReservationService::class
        );

        $this->app->bind(
            \App\Services\Contracts\EquipmentServiceInterface::class,
            \App\Services\EquipmentService::class
        );

        $this->app->bind(
            \App\Services\Contracts\UserServiceInterface::class,
            \App\Services\UserService::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
