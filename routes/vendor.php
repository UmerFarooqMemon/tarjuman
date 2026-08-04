<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Vendor Routes (portal)
|--------------------------------------------------------------------------
|
| Guard: vendor (provider: vendor_users)
| URL: /{locale}/vendor/...
|
*/

Route::group([
    'prefix' => LaravelLocalization::setLocale().'/vendor',
    'middleware' => [
        'vendor.guard',
        'localeSessionRedirect',
        'localizationRedirect',
        'localeViewPath',
    ],
], function () {
    Route::name('vendor.')->group(function () {
        Route::get('/', 'IndexController@index');

        Route::get('/auth/login', [
            'uses' => 'Auth\LoginController@showLoginForm',
            'as' => 'auth.login',
        ]);
        Route::post('/auth/login', [
            'uses' => 'Auth\LoginController@login',
            'as' => 'auth.login',
        ]);
        Route::any('/auth/logout', [
            'uses' => 'Auth\LoginController@logout',
            'as' => 'auth.logout',
        ]);

        Route::get('dashboard', [
            'uses' => 'DashboardController@index',
            'as' => 'dashboard.index',
        ]);
    });
});
