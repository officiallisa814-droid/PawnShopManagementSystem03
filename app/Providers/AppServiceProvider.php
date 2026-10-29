<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use App\Support\Notifications;

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
                    $shop = Setting::DEFAULTS;
                }
            }

            $view->with('shop', $shop);
        });

        // ២. Notifications សម្រាប់ layout តែមួយ
        View::composer('layouts.master', function ($view) {
            try {
                $view->with('notifs', Notifications::all());
            } catch (\Throwable $e) {
                $view->with('notifs', []);
            }
        });

        // ២. កំណត់ Gate សម្រាប់គ្រប់សិទ្ធិក្នុង config/roles.php
        foreach (array_keys(config('roles.permissions', [])) as $perm) {
            Gate::define($perm, fn($user) => $user->hasPermission($perm));
        }
    }
}