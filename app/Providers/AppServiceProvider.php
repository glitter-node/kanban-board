<?php

namespace App\Providers;

use App\Notifications\ResetPasswordCustom;
use App\Services\ExperimentService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('search', function (Request $request) {
            return Limit::perMinute(30)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('email-preverify', function (Request $request) {
            return Limit::perMinute(5)
                ->by($request->ip().'|'.mb_strtolower((string) $request->input('email')))
                ->response(function () {
                    return response()->json([
                        'message' => 'Too many verification requests. Please try again later.',
                    ], 429);
                });
        });

        ResetPassword::toMailUsing(function ($notifiable, string $token) {
            return (new ResetPasswordCustom($token))->toMail($notifiable);
        });

        Blade::directive('experiment', function ($expression) {
            return "<?php if(app(\\App\\Services\\ExperimentService::class)->beginExperiment({$expression}, auth()->user())): ?>";
        });

        Blade::directive('variant', function ($expression) {
            return "<?php if(app(\\App\\Services\\ExperimentService::class)->variantMatches({$expression})): ?>";
        });

        Blade::directive('endvariant', function () {
            return '<?php endif; ?>';
        });

        Blade::directive('endexperiment', function () {
            return '<?php app(\\App\\Services\\ExperimentService::class)->endExperiment(); endif; ?>';
        });

        View::composer(['layouts.app', 'layouts.guest', 'welcome'], function ($view): void {
            $view->with('frontendExperiments', app(ExperimentService::class)->frontendAssignments(auth()->user()));
        });
    }
}
