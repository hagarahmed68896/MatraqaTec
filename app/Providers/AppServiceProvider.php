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
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Share notifications with admin layout
        \Illuminate\Support\Facades\View::composer('layouts.admin', function ($view) {
            if (\Illuminate\Support\Facades\Auth::check()) {
                $notifications = \Illuminate\Support\Facades\Auth::user()->unreadNotifications()->latest()->take(5)->get();
                $unreadCount = \Illuminate\Support\Facades\Auth::user()->unreadNotifications()->count();
                $view->with('adminNotifications', $notifications)->with('adminUnreadCount', $unreadCount);
            } else {
                $view->with('adminNotifications', collect([]))->with('adminUnreadCount', 0);
            }
        });
    }
}
