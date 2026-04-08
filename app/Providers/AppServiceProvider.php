<?php

namespace App\Providers;

use App\Models\Project;
use Illuminate\Support\Facades\View;
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
        View::composer('layouts.app', function ($view) {
            if (auth()->check()) {
                $view->with('sidebarProjects', Project::where('owner_id', auth()->id())
                    ->orWhereHas('members', fn($q) => $q->where('user_id', auth()->id()))
                    ->get());
            }
        });
    }
}
