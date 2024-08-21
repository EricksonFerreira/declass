<?php

namespace App\Providers;

use App\Models\Booking;
use App\Services\BookingService;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        Paginator::useBootstrapFour();

        // $this->app->singleton(BookingService::class, function ($app) {
        //     $bookingService = new BookingService(new Booking());
        //     return $bookingService;
        // });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
