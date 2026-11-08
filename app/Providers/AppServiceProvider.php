<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\NotifikasiService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

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
        View::composer('partials.header', function ($view) {
            $user = Auth::user();

            $view->with('notifs', $user
                ? NotifikasiService::untuk($user)
                : ['items' => collect(), 'baru' => 0]);
        });
    }
}
