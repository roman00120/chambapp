<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

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
        RateLimiter::for('login', function (Request $request): Limit {
            $email = Str::lower($request->string('email')->toString());

            return Limit::perMinute(5)->by($email.'|'.$request->ip());
        });

        RateLimiter::for('register', fn (Request $request): Limit => Limit::perMinute(5)->by($request->ip()));

        RateLimiter::for('password-reset', function (Request $request): Limit {
            $email = Str::lower($request->string('email')->toString());

            return Limit::perMinute(3)->by($email.'|'.$request->ip());
        });

        RateLimiter::for('verification-email', fn (Request $request): Limit => Limit::perMinute(3)->by((string) $request->user()?->getAuthIdentifier()));

        RateLimiter::for('quotes', fn (Request $request): Limit => Limit::perMinute(10)->by((string) $request->user()?->getAuthIdentifier()));

        RateLimiter::for('payments', fn (Request $request): Limit => Limit::perMinute(5)->by((string) $request->user()?->getAuthIdentifier()));

        RateLimiter::for('reviews', fn (Request $request): Limit => Limit::perMinute(5)->by((string) $request->user()?->getAuthIdentifier()));

        RateLimiter::for('workflow', fn (Request $request): Limit => Limit::perMinute(20)->by((string) $request->user()?->getAuthIdentifier()));

        RateLimiter::for('admin-actions', fn (Request $request): Limit => Limit::perMinute(60)->by((string) $request->user()?->getAuthIdentifier()));

        RateLimiter::for('api-login', function (Request $request): Limit {
            $email = Str::lower($request->string('email')->toString());

            return Limit::perMinute(5)->by($email.'|'.$request->ip());
        });
        RateLimiter::for('api-register', fn (Request $request): Limit => Limit::perMinute(5)->by($request->ip()));
        RateLimiter::for('api-jobs', fn (Request $request): Limit => Limit::perMinute(10)->by((string) $request->user()?->getAuthIdentifier()));
        RateLimiter::for('api-polling', fn (Request $request): Limit => Limit::perMinute(30)->by((string) $request->user()?->getAuthIdentifier()));
        RateLimiter::for('api-read', fn (Request $request): Limit => Limit::perMinute(60)->by((string) $request->ip()));
        RateLimiter::for('api-accept', fn (Request $request): Limit => Limit::perMinute(12)->by((string) $request->user()?->getAuthIdentifier()));
        RateLimiter::for('api-workflow', fn (Request $request): Limit => Limit::perMinute(30)->by((string) $request->user()?->getAuthIdentifier()));
        RateLimiter::for('identity-verification-start', fn (Request $request): Limit => Limit::perHour(3)->by((string) $request->user()?->getAuthIdentifier()));
        RateLimiter::for('identity-verification-sync', fn (Request $request): Limit => Limit::perMinute(6)->by((string) $request->user()?->getAuthIdentifier()));
        RateLimiter::for('identity-verification-transfer', fn (Request $request): Limit => Limit::perMinute(20)->by(
            (string) ($request->user()?->getAuthIdentifier() ?? $request->ip()),
        ));
    }
}
