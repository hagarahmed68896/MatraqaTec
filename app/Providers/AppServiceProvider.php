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
        // Define Gates for permissions
        \Illuminate\Support\Facades\Gate::before(function ($user, $ability) {
            return $user->hasPermission($ability) ?: null;
        });

        // Share notifications with admin layout
        \Illuminate\Support\Facades\View::composer('layouts.admin', function ($view) {
            if (\Illuminate\Support\Facades\Auth::check()) {
                $userId = \Illuminate\Support\Facades\Auth::id();
                $notifications = \App\Models\Notification::where('user_id', $userId)
                    ->where('is_read', false)
                    ->latest()
                    ->take(5)
                    ->get();
                $unreadCount = \App\Models\Notification::where('user_id', $userId)
                    ->where('is_read', false)
                    ->count();
                $view->with('adminNotifications', $notifications)->with('adminUnreadCount', $unreadCount);
            } else {
                $view->with('adminNotifications', collect([]))->with('adminUnreadCount', 0);
            }
        });
    }
}
