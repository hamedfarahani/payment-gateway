<?php

namespace App\Providers;

use App\Services\CheckoutService;
use App\Services\Interfaces\CheckoutServiceInterface;
use Illuminate\Support\ServiceProvider;

class ServiceLayerProvider extends ServiceProvider
{

    public $bindings = [
        CheckoutServiceInterface::class            => CheckoutService::class,
    ];
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
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
