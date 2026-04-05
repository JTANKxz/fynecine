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
        \Illuminate\Support\Facades\View::composer('layouts.admin', function ($view) {
            $view->with('pending_tickets_count', \App\Models\Ticket::whereIn('status', ['unread', 'open'])->count());
            $view->with('pending_comments_count', \App\Models\Comment::where('approved', false)->count());
            $view->with('pending_requests_count', \App\Models\ContentRequest::where('status', 'pending')->count());
        });
    }
}
