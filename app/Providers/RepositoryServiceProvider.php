<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\Interfaces\LocationRepositoryInterface;
use App\Repositories\LocationRepository;
use App\Repositories\Interfaces\DateNightRepositoryInterface;
use App\Repositories\DateNightRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            LocationRepositoryInterface::class, 
            LocationRepository::class
        );
        $this->app->bind(
            DateNightRepositoryInterface::class, 
            DateNightRepository::class
        );
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
