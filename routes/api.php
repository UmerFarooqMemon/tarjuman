<?php

use App\Http\Controllers\Api\AddOnsController;
use App\Http\Controllers\Api\AuthoritiesController;
use App\Http\Controllers\Api\DeliverySpeedsController;
use App\Http\Controllers\Api\DocumentTypesController;
use App\Http\Controllers\Api\EstimateController;
use App\Http\Controllers\Api\LanguagesController;
use App\Http\Controllers\Api\PlatformSettingsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Website / general APIs use the env API_TOKEN (api.token middleware).
| Upcoming authenticated customer APIs will use auth:api (JWT).
|
*/

Route::middleware('api.token')->group(function () {
    Route::get('languages', [LanguagesController::class, 'index'])->name('api.languages.index');
    Route::get('document-types', [DocumentTypesController::class, 'index'])->name('api.document-types.index');
    Route::get('authorities', [AuthoritiesController::class, 'index'])->name('api.authorities.index');
    Route::get('add-ons', [AddOnsController::class, 'index'])->name('api.add-ons.index');
    Route::get('delivery-speeds', [DeliverySpeedsController::class, 'index'])->name('api.delivery-speeds.index');
    Route::get('platform-settings', [PlatformSettingsController::class, 'show'])->name('api.platform-settings.show');

    Route::post('estimate', [EstimateController::class, 'store'])->name('api.estimate');
});
