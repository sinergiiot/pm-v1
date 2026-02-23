<?php

namespace App\Providers;

use App\Models\Project;
use App\Models\Task;
use App\Models\TaskPriority;
use App\Models\TaskStatus;
use App\Listeners\UpdateSocialiteUserTokens;
use App\Observers\ProjectObserver;
use App\Observers\TaskObserver;
use App\Observers\TaskPriorityObserver;
use App\Observers\TaskStatusObserver;
use DutchCodingCompany\FilamentSocialite\Events\Login;
use Illuminate\Support\Facades\Event;
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
        Event::listen(Login::class, UpdateSocialiteUserTokens::class);
        Project::observe(ProjectObserver::class);
        Task::observe(TaskObserver::class);
        TaskStatus::observe(TaskStatusObserver::class);
        TaskPriority::observe(TaskPriorityObserver::class);
    }
}
