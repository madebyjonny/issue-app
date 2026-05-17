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

                // Share channels and members when inside a project context
                $project = $view->getData()['project'] ?? null;
                if ($project instanceof Project) {
                    $view->with('sidebarChannels', $project->channels()
                        ->where(fn($q) => $q
                            ->whereHas('members', fn($q2) => $q2->where('user_id', auth()->id()))
                            ->orWhere('is_private', false)
                        )
                        ->get());

                    $view->with('sidebarMembers', $project->members()->get()
                        ->merge($project->owner ? collect([$project->owner]) : collect())
                        ->unique('id'));
                }
            }
        });
    }
}
