<?php

use Illuminate\Support\Facades\Route;

// use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

// Route::get('/', function () {
//     return 'Admin';
// });

Route::group([
    'prefix' => '/admin',
    // 'prefix' => LaravelLocalization::setLocale().'/admin',
    'middleware' => [
        'admin.guard',
        // 'localeSessionRedirect',
        // 'localizationRedirect',
        // 'localeViewPath',
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
                Route::resource('roles', 'RolesController')->except(['show']);
                Route::resource('site-settings', 'SiteSettingsController')->only(['index', 'update']);
            }
        );
    });
