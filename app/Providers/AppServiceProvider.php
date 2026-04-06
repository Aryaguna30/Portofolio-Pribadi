<?php

namespace App\Providers;

use HTMLPurifier;
use HTMLPurifier_Config;
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
        // Register HTMLPurifier as a singleton so ProjectController can call app('purifier')
        $this->app->singleton('purifier', function () {
            $config = HTMLPurifier_Config::createDefault();
            $config->set('HTML.Allowed', 'p,br,strong,em,ul,ol,li,a[href|target|rel],h2,h3,h4,blockquote,code,pre');
            $config->set('HTML.TargetBlank', true);
            $config->set('HTML.Nofollow', true);
            $config->set('URI.AllowedSchemes', ['http' => true, 'https' => true]);
            $config->set('Cache.SerializerPath', storage_path('app/purifier'));
            return new HTMLPurifier($config);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Force HTTPS in production
        if ($this->app->environment('production')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

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
