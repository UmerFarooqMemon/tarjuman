<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Vendor Routes (portal)
|--------------------------------------------------------------------------
|
| Vendor dashboard and authentication routes will be registered here.
| Guard: vendor (provider: vendor_users)
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
        // Vendor auth & dashboard routes will be added in a follow-up.
    });
});
