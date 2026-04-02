<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
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
        // contact: 3 requests per hour per IP
        RateLimiter::for('contact', function (Request $request) {
            return Limit::perHour(3)->by($request->ip())
                ->response(fn() => back()->withErrors([
                    'rate_limit' => 'Terlalu banyak percobaan. Silakan coba lagi dalam 1 jam.'
                ]));
        });

        // login: 5 requests per minute per IP
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        // coding-stats: 30 requests per minute per IP
        RateLimiter::for('coding-stats', function (Request $request) {
            return Limit::perMinute(30)->by($request->ip());
        });

        // cv-download: 10 requests per minute per IP
        RateLimiter::for('cv-download', function (Request $request) {
            return Limit::perMinute(10)->by($request->ip());
        });
    }
}
