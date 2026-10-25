<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        View::composer('*', function ($view) {
            static $shop = null;

            if ($shop === null) {
                try {
                    $shop = Setting::all_();
                } catch (\Throwable $e) {
                    // មុន migrate ឬ Table settings មិនទាន់មាន
                    $shop = Setting::DEFAULTS;
                }
            }

            $view->with('shop', $shop);
        });
    }
}