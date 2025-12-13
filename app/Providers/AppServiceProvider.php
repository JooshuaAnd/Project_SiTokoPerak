<?php

namespace App\Providers;
use App\Http\Middleware\RoleCheck; // pastikan ada use ini di atas
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route; // pastikan ada use ini di atas
use Illuminate\Pagination\Paginator;

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
        Route::aliasMiddleware('role', RoleCheck::class); // <-- ini yang nambahin alias 'role'
        Paginator::useBootstrapFive();

        // Load helper buat count cart
        foreach (glob(app_path('Helpers/*.php')) as $filename) {
            require_once $filename;
        }
    }
}
