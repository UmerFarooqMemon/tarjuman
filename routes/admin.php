<?php

use Illuminate\Support\Facades\Route;

// use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

// Route::get('/', function () {
//     return 'Admin';
// });

Route::group([
    // 'prefix' => '/admin',
    'prefix' => LaravelLocalization::setLocale().'/admin',
    'middleware' => [
        'admin.guard',
        'localeSessionRedirect',
        'localizationRedirect',
        'localeViewPath',
    ]], function () {

        Route::name('admin.')->group(
            function () {

                Route::get('/', 'IndexController@index');

                // to show login form
                Route::get('/auth/login', [
                    'uses' => 'Auth\LoginController@showLoginForm',
                    'as' => 'auth.login',
                ]);

                // login form submits to this route
                Route::post('/auth/login', [
                    'uses' => 'Auth\LoginController@login',
                    'as' => 'auth.login',
                ]);

                // logs out admin user
                // it was post method before I recieved MethodNotAllowedHttpException
                Route::any('/auth/logout', [
                    'uses' => 'Auth\LoginController@logout',
                    'as' => 'auth.logout',
                ]);

                // Password reset routes
                Route::post('/password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail')->name('password.email');
                Route::post('/password/reset', 'Auth\ResetPasswordController@reset');
                Route::get('/password/reset', 'Auth\ForgotPasswordController@showLinkRequestForm')->name('password.request');
                Route::get('/password/reset/{token}', 'Auth\ResetPasswordController@showResetForm')->name('password.reset');

                // shows dashboard
                Route::get('dashboard', [
                    'uses' => 'DashboardController@index',
                    'as' => 'dashboard.index',
                ]);

                Route::get('update-profile', 'AdministratorsController@editProfile')->name('update-profile');
                Route::put('update-profile', 'AdministratorsController@updateProfile')->name('update-profile.store');
                Route::resource('administrators', 'AdministratorsController');
                Route::post('change-status', 'AdministratorsController@changeStatus')->name('update-status');
                Route::resource('vendors', 'VendorsController');
                Route::post('vendors/change-status', 'VendorsController@changeStatus')->name('vendors.update-status');

                Route::resource('languages', 'LanguagesController')->except(['show', 'create', 'edit']);
                Route::post('languages/change-status', 'LanguagesController@changeStatus')->name('languages.update-status');

                Route::resource('currencies', 'CurrenciesController')->except(['show', 'create', 'edit']);
                Route::post('currencies/change-status', 'CurrenciesController@changeStatus')->name('currencies.update-status');

                Route::resource('document-types', 'DocumentTypesController')->except(['show', 'create', 'edit']);
                Route::post('document-types/change-status', 'DocumentTypesController@changeStatus')->name('document-types.update-status');

                Route::resource('authorities', 'AuthoritiesController')->except(['show', 'create', 'edit']);
                Route::post('authorities/change-status', 'AuthoritiesController@changeStatus')->name('authorities.update-status');

                Route::resource('add-ons', 'AddOnsController')->except(['show', 'create', 'edit']);
                Route::post('add-ons/change-status', 'AddOnsController@changeStatus')->name('add-ons.update-status');

                Route::resource('delivery-speeds', 'DeliverySpeedsController')->except(['show', 'create', 'edit']);
                Route::post('delivery-speeds/change-status', 'DeliverySpeedsController@changeStatus')->name('delivery-speeds.update-status');

                Route::resource('pricing-rules', 'PricingRulesController')->except(['show']);
                Route::post('pricing-rules/change-status', 'PricingRulesController@changeStatus')->name('pricing-rules.update-status');

                Route::resource('roles', 'RolesController')->except(['show']);
                Route::resource('site-settings', 'SiteSettingsController')->only(['index', 'update']);
                Route::resource('platform-settings', 'PlatformSettingsController')->only(['index', 'update']);

                Route::resource('plans', 'PlansController')->except(['show', 'create', 'edit']);
                Route::post('plans/change-status', 'PlansController@changeStatus')->name('plans.update-status');

                Route::get('orders', 'OrdersController@index')->name('orders.index');
                Route::get('orders/{order}', 'OrdersController@show')->name('orders.show');
                Route::post('orders/{order}/assign', 'OrdersController@assign')->name('orders.assign');

                Route::get('notifications', 'NotificationsController@index')->name('notifications.index');
                Route::post('notifications/read-all', 'NotificationsController@markAllRead')->name('notifications.read-all');
                Route::post('notifications/{id}/read', 'NotificationsController@markRead')->name('notifications.read');
                Route::delete('notifications/{id}', 'NotificationsController@destroy')->name('notifications.destroy');
                Route::post('broadcasting/auth', [\Illuminate\Broadcasting\BroadcastController::class, 'authenticate'])->middleware('auth:admin')->name('broadcasting.auth');

                Route::get('cms/pages', 'Cms\PageController@index')->name('cms.pages.index');
                Route::get('cms/pages/{page}', 'Cms\PageController@show')->name('cms.pages.show');
                Route::get('cms/pages/{page}/sections/{section}/edit', 'Cms\PageController@editSection')->name('cms.pages.sections.edit');
                Route::put('cms/pages/{page}/sections/{section}', 'Cms\PageController@updateSection')->name('cms.pages.sections.update');
                Route::post('cms/pages/{page}/sections/{section}/toggle', 'Cms\PageController@toggleSection')->name('cms.pages.sections.toggle');
            }
        );
    });
