<?php

use App\Http\Controllers\Api\TelegramBindingController;
use App\Http\Controllers\Api\V1\Payments\PaymentsController;
use App\Http\Controllers\Api\V1\Payments\Providers\BlvckController;
use App\Http\Controllers\Api\V1\Payments\WithdrawsController;
use App\Http\Controllers\Api\V1\Slots\UserSlotsController;
use App\Http\Controllers\Api\V1\Tournaments\TournamentsController;
use App\Http\Controllers\B2BSlotsController;
use App\Http\Controllers\MobuleSlotController;
use App\Http\Controllers\TelegramController;
use App\Services\Telegram\TelegramService;
use Illuminate\Support\Facades\Route;

// b2b api routes
Route::get('b2b/fetch-and-store', [B2BSlotsController::class, 'fetchAndStore']);
Route::get('b2b/slots', [B2BSlotsController::class, 'getSlots']);
Route::get('b2b/show/{id}', [B2BSlotsController::class, 'show']);
Route::post('/slots/ifd783b2bcallback', [B2BSlotsController::class, 'callback']);
Route::get('b2b/providers', [B2BSlotsController::class, 'providers']);
Route::get('slot/info/{id}', [B2BSlotsController::class, 'info']);

// mobule api routes
Route::get('mobule/fetch-and-store', [MobuleSlotController::class, 'fetchAndStore']);
Route::get('mobule/slots', [MobuleSlotController::class, 'getSlots']);
Route::get('mobule/show/{id}', [MobuleSlotController::class, 'show']);
Route::post('mobule/callback/{method}', [MobuleSlotController::class, 'callback']);
Route::get('mobule/providers', [MobuleSlotController::class, 'providers']);


Route::post('/telegram/getUserId', [TelegramController::class, 'getUserId']);
Route::post('/telegram/webhook', [TelegramController::class, 'handle']);
Route::get('/telegram/set-webhook', function (TelegramService $telegram) {
    $webhook = $telegram->setWebhook(config('app.url') . '/api/telegram/webhook');
    return response()->json($webhook);
});

Route::post('/telegram/binding/generate', [TelegramBindingController::class, 'generate']);

Route::prefix('deposit')->group(function () {
    Route::prefix('webhook')->group(function () {
        Route::post('blvckpay', [BlvckController::class, 'depositWebhook']);
    });
});

Route::prefix('v1')
    ->group(function () {
        Route::prefix('user')
            ->middleware('web')
            ->group(function () {
                Route::prefix('{userId}')
                    ->middleware('only-current-user-in-path')
                    ->group(function () {
                        Route::prefix('payments')
                            ->group(function () {
                                Route::post('list', [PaymentsController::class, 'paymentsMethods']);
                            });
                        Route::prefix('slots')->group(function () {
                            Route::get('last', [UserSlotsController::class, 'last']);
                        });
                    });

                Route::prefix('withdraws')
                    ->middleware('only-current-user-filter')
                    ->group(function () {
                        Route::post('list', [WithdrawsController::class, 'list']);
                    });

                Route::prefix('payments')
                    ->middleware('only-current-user-filter')
                    ->group(function () {
                        Route::post('list', [PaymentsController::class, 'list']);
                    });

            });
        Route::prefix('payments')
            ->group(function () {
                Route::post('callback/{secret}', [PaymentsController::class, 'callback'])
                    ->name('vpi.v1.payments.callback');
            });
        Route::prefix('withdraws')
            ->group(function () {
                Route::post('list', [WithdrawsController::class, 'list']);
            });
        Route::prefix('tournaments')->group(function () {
            Route::post('list', [TournamentsController::class, 'list']);
        });
    });