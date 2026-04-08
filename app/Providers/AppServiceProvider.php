<?php

namespace App\Providers;

use App\Models\Project;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

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
        Passport::authorizationView(function ($parameters) {
            return view('mcp.authorize', $parameters);
        });

        View::composer('layouts.app', function ($view) {
            if (auth()->check()) {
                $view->with('sidebarProjects', Project::where('owner_id', auth()->id())
                    ->orWhereHas('members', fn($q) => $q->where('user_id', auth()->id()))
                    ->get());
            }
        });
    }
}
