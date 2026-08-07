<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',

        then: function () {

            /*
            |--------------------------------------------------------------------------
            | Admin Routes
            |--------------------------------------------------------------------------
            */

            Route::middleware('web')
                // ->prefix('admin')
                ->namespace('App\Http\Controllers\Admin')
                ->group(base_path('routes/admin.php'));

            Route::middleware('web')
                ->namespace('App\Http\Controllers\Vendor')
                ->group(base_path('routes/vendor.php'));
        }
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo(function (Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return null;
            }

            if (preg_match('#(^|/)vendor(/|$)#', $request->path())) {
                return route('vendor.auth.login');
            }

            return route('admin.auth.login');
        });
        $middleware->redirectUsersTo(function (Request $request) {
            if (preg_match('#(^|/)vendor(/|$)#', $request->path())) {
                return route('vendor.dashboard.index');
            }

            return route('admin.dashboard.index');
        });

        $middleware->alias([
            'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
            'admin.guard' => \App\Http\Middleware\SetAdminGuard::class,
            'vendor.guard' => \App\Http\Middleware\SetVendorGuard::class,
            'api.token' => \App\Http\Middleware\AuthenticateApiToken::class,
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'localize' => \Mcamara\LaravelLocalization\Middleware\LaravelLocalizationRoutes::class,
            'localizationRedirect' => \Mcamara\LaravelLocalization\Middleware\LaravelLocalizationRedirectFilter::class,
            'localeSessionRedirect' => \Mcamara\LaravelLocalization\Middleware\LocaleSessionRedirect::class,
            'localeViewPath' => \Mcamara\LaravelLocalization\Middleware\LaravelLocalizationViewPath::class,
            'LanguageSwitcher' => \App\Http\Middleware\LanguageSwitcher::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(function (Request $request, \Throwable $e) {
            return $request->is('api/*') || $request->expectsJson();
        });
    })->create();
