<?php

namespace App\Providers;

use App\Models\Admin;
// use App\Models\Page;
use App\Models\SiteSetting;
use App\Support\CatalogCache;
use Illuminate\Support\Facades\Gate;
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
        Gate::before(function ($user, string $ability) {
            if ($user instanceof Admin && $user->is_system_admin) {
                return true;
            }

            return null;
        });


        view()->composer('*', function ($view) {
            $siteSettings = CatalogCache::siteSettings()
                ?? SiteSetting::query()->firstOrCreate(
                    ['id' => 1],
                    [
                        'site_title' => config('app.name', 'Admin'),
                        'contact_email' => 'support@admin.com',
                        'currency' => 'AED',
                        'primary_color' => '#7367F0',
                        'primary_color_end' => '#9E95F5',
                        'primary_color_angle' => 135,
                        'secondary_color' => '#A8AAAE',
                        'secondary_color_end' => '#D2D2D6',
                        'secondary_color_angle' => 135,
                        'primary_button_color' => '#7367F0',
                        'primary_button_color_end' => '#9E95F5',
                        'primary_button_color_angle' => 135,
                        'secondary_button_color' => '#A8AAAE',
                        'secondary_button_color_end' => '#D2D2D6',
                        'secondary_button_color_angle' => 135,
                        'primary_button_text_color' => '#FFFFFF',
                        'secondary_button_text_color' => '#FFFFFF',
                        'primary_button_border_color' => '#7367F0',
                        'secondary_button_border_color' => '#A8AAAE',
                    ]
                );

            $view->with('siteSettings', $siteSettings);
            $view->with('adminsCount', (int) Admin::count());
        });
    }
}
