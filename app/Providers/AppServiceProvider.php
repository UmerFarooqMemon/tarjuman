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
                        'primary_color' => '#000000',
                        'primary_color_end' => '#000000',
                        'primary_color_angle' => 135,
                        'secondary_color' => '#FFFFFF',
                        'secondary_color_end' => '#FFFFFF',
                        'secondary_color_angle' => 135,
                        'primary_button_color' => '#000000',
                        'primary_button_color_end' => '#000000',
                        'primary_button_color_angle' => 135,
                        'secondary_button_color' => '#FFFFFF',
                        'secondary_button_color_end' => '#FFFFFF',
                        'secondary_button_color_angle' => 135,
                        'primary_button_text_color' => '#FFFFFF',
                        'secondary_button_text_color' => '#000000',
                        'primary_button_border_color' => '#000000',
                        'secondary_button_border_color' => '#000000',
                    ]
                );

            $view->with('siteSettings', $siteSettings);
            $view->with('adminsCount', (int) Admin::count());
        });
    }
}
