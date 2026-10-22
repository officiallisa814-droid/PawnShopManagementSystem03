<?php

namespace App\Providers;
use Illuminate\Support\Facades\View;
use App\Models\Setting;

use Illuminate\Support\ServiceProvider;

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
        View::composer('*', function ($view) {
            try {
                $view->with('shop', Setting::all_());
            } catch (\Throwable $e) {
                $view->with('shop', Setting::DEFAULTS);
            }
        });
    }
}
