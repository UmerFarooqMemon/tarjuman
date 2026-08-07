<?php

use App\Http\Controllers\Api\AddOnsController;
use App\Http\Controllers\Api\AuthoritiesController;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\CmsPageController;
use App\Http\Controllers\Api\DeliverySpeedsController;
use App\Http\Controllers\Api\DocumentTypesController;
use App\Http\Controllers\Api\EstimateController;
use App\Http\Controllers\Api\LanguagesController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PlansController;
use App\Http\Controllers\Api\PlatformSettingsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Website / general APIs use the env API_TOKEN (api.token middleware).
| Authenticated customer APIs use auth:api (JWT).
|
*/

Route::middleware('api.token')->group(function () {
    Route::get('languages', [LanguagesController::class, 'index'])->name('api.languages.index');
    Route::get('document-types', [DocumentTypesController::class, 'index'])->name('api.document-types.index');
    Route::get('authorities', [AuthoritiesController::class, 'index'])->name('api.authorities.index');
    Route::get('add-ons', [AddOnsController::class, 'index'])->name('api.add-ons.index');
    Route::get('delivery-speeds', [DeliverySpeedsController::class, 'index'])->name('api.delivery-speeds.index');
    Route::get('platform-settings', [PlatformSettingsController::class, 'show'])->name('api.platform-settings.show');
    Route::get('cms/pages/{slug}', [CmsPageController::class, 'show'])->name('api.cms.pages.show');
    Route::get('plans', [PlansController::class, 'index'])->name('api.plans.index');

    Route::post('estimate', [EstimateController::class, 'store'])->name('api.estimate');
    Route::post('orders', [OrderController::class, 'store'])->name('api.orders.store');
    Route::get('orders/{orderId}', [OrderController::class, 'show'])->name('api.orders.show');

    Route::post('orders/payments/{driver}/callback', [OrderController::class, 'paymentCallback'])
        ->whereIn('driver', ['paytabs', 'tap', 'noon', 'amazon_ps'])
        ->name('api.orders.payments.callback');

    Route::post('auth/register/individual', [AuthController::class, 'registerIndividual'])->name('api.auth.register.individual');
    Route::post('auth/register/enterprise', [AuthController::class, 'registerEnterprise'])->name('api.auth.register.enterprise');
    Route::post('auth/login', [AuthController::class, 'login'])->name('api.auth.login');
});

Route::middleware(['auth:api'])->group(function () {
    Route::get('auth/me', [AuthController::class, 'me'])->name('api.auth.me');
    Route::post('auth/me', [AuthController::class, 'updateMe'])->name('api.auth.me.update');
});

// Signed preview bootstrap for CMS admin iframe (no API token; signature is the auth).
Route::get('cms/preview/{slug}', [CmsPageController::class, 'preview'])->name('api.cms.preview');
