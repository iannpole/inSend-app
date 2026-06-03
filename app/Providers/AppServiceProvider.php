<?php

namespace App\Providers;

use App\Models\PersonalAccessToken;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;
use App\Services\AI\AiServiceInterface;
use App\Services\AI\RecipeBotService;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(AiServiceInterface::class, RecipeBotService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);

        // Bagikan aktivitas terbaru ke komponen layout admin (seperti notifikasi)
        \Illuminate\Support\Facades\View::composer('admin.layouts.app', function ($view) {
            $globalActivities = \App\Models\ActivityLog::orderBy('created_at', 'desc')->take(5)->get();
            $view->with('globalActivities', $globalActivities);
        });
    }
}
