<?php

namespace App\Providers;

use App\Interfaces\CartRepositoryInterface;
use App\Interfaces\CheckoutRepositoryInterface;
use App\Interfaces\ShoeRepositoryInterface;
use App\Repositories\CartRepository;
use App\Repositories\CheckoutRepository;
use App\Repositories\ShoeRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ShoeRepositoryInterface::class, ShoeRepository::class);
        $this->app->bind(CartRepositoryInterface::class, CartRepository::class);
        $this->app->bind(CheckoutRepositoryInterface::class, CheckoutRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
