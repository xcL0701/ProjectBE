<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Payment;
use App\Observers\PaymentObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Force https di production
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        // Observe model Payment
        Payment::observe(PaymentObserver::class);
    }
}
