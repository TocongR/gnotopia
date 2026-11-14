<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Vite;
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
        Vite::prefetch(concurrency: 3);

        // These route parameters are always database ids. Anything else (letters, or a number
        // too big for a bigint) is a 404, so malformed URLs never reach the database.
        foreach (['user', 'module', 'lesson', 'quiz', 'attempt'] as $parameter) {
            Route::pattern($parameter, '[0-9]{1,18}');
        }
    }
}
