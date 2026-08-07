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

        Route::middleware('auth:vendor')->group(function () {
            Route::get('orders', 'OrdersController@index')->name('orders.index');
            Route::get('orders/data', 'OrdersController@indexData')->name('orders.data');
            Route::get('orders/discover', 'OrdersController@discover')->name('orders.discover');
            Route::get('orders/open', fn () => redirect()->route('vendor.orders.discover'))->name('orders.open');
            Route::get('orders/mine', 'OrdersController@mine')->name('orders.mine');
            Route::get('orders/{order}', 'OrdersController@show')->name('orders.show');
            Route::get('orders/{order}/details', 'OrdersController@details')->name('orders.details');
            Route::post('orders/{order}/accept', 'OrdersController@accept')->name('orders.accept');
            Route::post('orders/{order}/confirm', 'OrdersController@confirm')->name('orders.confirm');
            Route::post('orders/{order}/send-payment-link', 'OrdersController@sendPaymentLink')->name('orders.send-payment-link');
            Route::post('orders/{order}/complete', 'OrdersController@complete')->name('orders.complete');
            Route::post('orders/{order}/documents', 'OrderDocumentsController@store')->name('orders.documents.store');
            Route::delete('orders/{order}/documents/{document}', 'OrderDocumentsController@destroy')->name('orders.documents.destroy');

            Route::get('orders/{order}/documents/{document}/preview', 'OrderDocumentsController@preview')
                ->name('orders.documents.preview');
            Route::get('orders/{order}/documents/{document}/download', 'OrderDocumentsController@download')
                ->name('orders.documents.download');
            Route::get('orders/{order}/documents/{document}/content', 'OrderDocumentsController@content')
                ->name('orders.documents.content');
            Route::get('orders/{order}/documents/{document}/stream', 'OrderDocumentsController@stream')
                ->name('orders.documents.stream');

            Route::get('notifications', 'NotificationsController@index')->name('notifications.index');
            Route::post('notifications/read-all', 'NotificationsController@markAllRead')->name('notifications.read-all');
            Route::post('notifications/{id}/read', 'NotificationsController@markRead')->name('notifications.read');
            Route::delete('notifications/{id}', 'NotificationsController@destroy')->name('notifications.destroy');
            Route::post('broadcasting/auth', [\Illuminate\Broadcasting\BroadcastController::class, 'authenticate'])->name('broadcasting.auth');
        });
    });
});
