<?php

use App\Http\Controllers\Api\V1\Payments\WithdrawsController;
use App\Http\Controllers\FakeAuthController;
use App\Http\Controllers\B2BSlotsController;
use App\Http\Controllers\MobuleSlotController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Middleware\OnlyInternalMiddleware;
use FKS\Web\Http\Controllers\WebController;
use Illuminate\Support\Facades\Route;

Route::get('web-view', [WebController::class, 'index']);
Route::post('web-view', [WebController::class, 'execute']);

Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::get('consume', [AuthController::class, 'consume']);
Route::post('/password/email', [AuthController::class, 'sendResetLinkEmail']);
Route::post('/password/reset', [AuthController::class, 'passwordReset'])->name('reset.password');

Route::get('/user', [AuthController::class, 'user']);

Route::middleware('auth')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('b2b/load', [B2BSlotsController::class, 'loadSlot']);
    Route::post('mobule/load', [MobuleSlotController::class, 'loadSlot']);
});

Route::get('/auth/fake/{id}', [FakeAuthController::class, 'fakeAuth']);
Route::get('/r/{unique_id}', 'ReferralController@setReferral');
Route::post('/vk/handle', 'UserController@repostVK');

Route::group(['prefix' => 'tournament'], function () {
    Route::get('leaders', 'TournamentController@leaders');
    Route::get('timer', 'TournamentController@timer');
    Route::get('live', 'TournamentController@live');
});

Route::group(['prefix' => 'payment'], function () {
    Route::post('fkwallet', 'PaymentController@handle');
    Route::get('success', 'PaymentController@paymentSuccess');
});

//spinpay
Route::post('/create/spinpay', 'PaymentController@createSpinPay');
Route::post('/callback/spinpay68fsdd2', 'PaymentController@callbackSpinPay');

//idm
//Route::post('/create/idm', 'PaymentController@createIdm');
//Route::post('/callback/idm243234', 'PaymentController@callbackIdm');

//RoyalFinance
//Route::post('/create/royalfinance', 'PaymentController@createRoyalFinance');
//Route::post('/callback/rf', 'PaymentController@callbackRoyalFinance');

//1plat
Route::post('/create/1plat', 'PaymentController@create1plat');
Route::post('/callback/1plat', 'PaymentController@callback1plat');

//paradise
Route::post('/create/paradise', 'PaymentController@createParadise');
Route::post('/callback/paradise', 'PaymentController@callbackParadise');

//test sms tg
Route::get('/testapi/apiroutes', 'PaymentController@TestApiRoutes');

//payou
//Route::post('create/payou', 'PaymentController@createPayou');
//Route::post('/callback/payou', 'PaymentController@callbackPayou');

//expay
Route::post('callback/expay34584734343434', 'PaymentController@handleExpay');

//aifory
//Route::post('/payment/callback-aifory4234234/{id}', 'PaymentController@callbackAifory');
//Route::post('/payment/callback-aifory83457432/{id}', 'PaymentController@callbackAiforyCrypto');

//Route::post('/withdraw/euphoriainc39840309', 'PaymentController@callbackAiforyWithdraw');
//Route::post('api/withdraw/euphoriainc39840309', 'PaymentController@callbackAiforyWithdraw');

//serjPay
//Route::post('/payment/cancel', 'PaymentController@serjCancel');
//Route::post('/payment/confirm', 'PaymentController@serjConfirm');
// Route::post('/api/analprobka8543', 'PaymentController@serjPayCallback');

// Grow
//Route::post('/create/grow', 'PaymentController@createGrow');
//Route::post('/callback/grow', 'PaymentController@callbackGrow');

//GTX
//Route::post('/create/gtx', 'PaymentController@createGtx');
//Route::post('/callback/gtx', 'PaymentController@callbackGtx');

// CryptoBot
Route::post('/callback/cryptobot', 'PaymentController@callbackCryptobot');

// Gotham
//Route::post('/create/gotham', 'PaymentController@createGotham');
//Route::post('/callback/gotham', 'PaymentController@callbackGotham');

// Transgran
//Route::post('/create/transgran', 'PaymentController@createTransgran');
//Route::post('/callback/transgran', 'PaymentController@callbackTransgran');

// Nirvana
//Route::post('/create/nirvana', 'PaymentController@createNirvana');
//Route::get('/callback/nirvana', 'PaymentController@callbackNirvana');

// Nirvana
//Route::post('/change/toWin', 'PaymentController@toWin');

// Repay
//Route::post('/create/repay', 'PaymentController@createRepay');
//Route::post('/callback/repay', 'PaymentController@callbackRepay');

// p2plab
//Route::post('/create/p2plab', 'PaymentController@createP2plab');
//Route::post('/callback/p2plab', 'PaymentController@callbackP2plab');

// eightpay
//Route::post('/create/eightpay', 'PaymentController@createEightpay');
//Route::post('/callback/eightpay', 'PaymentController@callbackEightpay');

Route::group(['prefix' => 'withdraw'], function () {
    Route::post('handle', 'WithdrawController@fkwalletHandle');
    Route::post('auto-withdraw', 'WithdrawController@autoWithdraw');
    Route::post('spinpay/callback', 'WithdrawController@callbackSpinPay');
    Route::post('callback5466653234523543354', 'WithdrawController@callback');
});

Route::group(['prefix' => 'ranks'], function () {
    Route::post('/get', 'UserController@ranks');
});

Route::group(['prefix' => 'api', 'middleware' => 'secretKey'], function () {
    Route::get('/getTimer', 'TimerController@timer');
    Route::post('/fake', 'FakeController@fake');
    // withdraws
    Route::group(['prefix' => 'withdraws', 'namespace' => 'Api'], function () {
        Route::post('/get', 'WithdrawsController@get');
        Route::post('/setStatus', 'WithdrawsController@setStatus');
    });
});

Route::group(['prefix' => 'bonus', 'middleware' => 'auth'], function () {
    Route::post('/init', 'BonusController@init');
    Route::post('/checkReposts', 'BonusController@checkReposts');
    Route::post('/transfer', 'BonusController@transfer');
    Route::post('/take', 'BonusController@take');
});

Route::group(['prefix' => 'plinko'], function () {
    Route::post('/init', 'PlinkoController@getMultipliers');
    Route::post('/play', 'PlinkoController@play');
});

Route::group(['prefix' => 'user'], function () {
    Route::get('/init', 'UserController@init');
    Route::get('/logout', 'UserController@logout');
    Route::post('/videocard', 'UserController@videocardUpdate');
    Route::post('/fingerprint', 'UserController@fingerprintUpdate');
});

Route::group(['prefix' => 'auth'], function () {
    Route::get('/{provider}', ['as' => 'login', 'uses' => 'Auth\VkController@login']);
    Route::get('/{provider}/handle', 'Auth\VkController@callback');
});

Route::group(['prefix' => 'referral', 'middleware' => 'auth'], function () {
    Route::post('/get', 'ReferralController@init');
    Route::post('/take', 'ReferralController@take');
    Route::post('/bonus/take', 'ReferralController@takeMilestoneBonus');
});


Route::group(['prefix' => 'deposit', 'middleware' => 'auth'], function () {
    Route::post('/init', 'Admin\DepositsController@init');
    Route::post('/take', 'Admin\DepositsController@take');
});


Route::group(['prefix' => 'dice', 'middleware' => 'auth'], function () {
    Route::post('/bet', 'DiceController@bet');
});

Route::group(['prefix' => 'wheel', 'middleware' => 'auth'], function () {
    Route::post('/start', 'WheelController@play');
});

Route::group(['prefix' => 'bubbles', 'middleware' => 'auth'], function () {
    Route::post('/play', 'BubblesController@play');
});

Route::group(['prefix' => 'mines', 'middleware' => 'auth'], function () {
    Route::post('/init', 'MinesController@init');
    Route::post('/start', 'MinesController@createGame');
    Route::post('/open', 'MinesController@openPath');
    Route::post('/take', 'MinesController@take');
});

Route::group(['prefix' => 'withdraw', 'middleware' => 'auth'], function () {
    Route::post('/create', 'WithdrawController@create');
    Route::post('/decline', 'WithdrawController@decline');
});

Route::group(['prefix' => 'payment', 'middleware' => 'auth'], function () {
    Route::post('/create', 'PaymentController@create');
    Route::post('/worker', 'PaymentController@workerBalance');
});

Route::group(['prefix' => 'promo', 'middleware' => 'auth'], function () {
    Route::post('/activate', 'PromoController@activate');
    Route::post('/create', 'PromoController@create');
});

Route::group(['prefix' => 'cashback', 'middleware' => 'auth'], function () {
    Route::post('/init', 'CashbackController@cashback');
    Route::post('/getCashback', 'CashbackController@getCashback');
});

Route::group(['prefix' => 'slots'], function () {
    Route::post('init', 'SlotsController@init');
    Route::post('get', 'SlotsController@getSlotWithPagenate');
    Route::post('count', 'SlotsController@countSlots');
    Route::post('getRandom', 'SlotsController@getRandom');
    Route::post('load', 'SlotsController@loadSlot');
});

Route::group(['prefix' => 'admin', 'namespace' => 'Admin', 'middleware' => 'auth'], function () {
    Route::group(['middleware' => 'access:admin'], function () {
        Route::post('/load', 'AdminController@load');
        Route::get('/', 'IndexController@index')->name('admin.index');
        Route::get('/users', 'UsersController@index')->name('admin.users');
        Route::get('/bots', 'BotsController@index')->name('admin.bots');
        Route::post('/versionUpdate', 'AdminController@versionUpdate');
        Route::post('/slotsUpdate', 'AdminController@slotsUpdate');
        Route::post('/getUserByMonth', 'IndexController@getUserByMonth');
        Route::post('/getUserStatsByMonth', 'IndexController@getUserStatsByMonth');
        Route::post('/getDepsByMonth', 'IndexController@getDepsByMonth');
        Route::post('/getWithdrawByMonth', 'IndexController@getWithdrawByMonth');
        Route::post('/getProfitByMonth', 'IndexController@getProfitByMonth');
        Route::post('/getAllStatsByMonth', 'IndexController@getAllStatsByMonth');
        Route::post('/getVKinfo', 'IndexController@getVK');
        Route::post('/getCountry', 'IndexController@getCountry');

        Route::group(['prefix' => 'promocodes'], function () {
            Route::get('/', 'PromocodeController@index')->name('admin.promocodes');
            Route::get('/create', 'PromocodeController@create')->name('admin.promocodes.create');
            Route::post('/create', 'PromocodeController@createPost');
            Route::get('/delete/{id}', 'PromocodeController@delete')->name('admin.promocodes.delete');
        });

        Route::group(['prefix' => 'users'], function () {
            Route::get('/edit/{id}', 'UsersController@edit')->name('admin.users.edit');
            Route::post('/edit/{id}', 'UsersController@editPost');
            Route::get('/create/{type}/{id}', 'UsersController@createFake')->name('admin.users.createFake');
            Route::post('/create/{type}/{id}', 'UsersController@addFake');
            Route::get('/delete/{id}', 'UsersController@delete')->name('admin.users.delete');
            Route::post('/checker', 'UsersController@checker');
        });

        Route::group(['prefix' => 'bots'], function () {
            Route::get('/create', 'BotsController@create')->name('admin.bots.create');
            Route::post('/create', 'BotsController@createPost');
            Route::get('/edit/{id}', 'BotsController@edit')->name('admin.bots.edit');
            Route::post('/edit/{id}', 'BotsController@editPost');
            Route::get('/delete/{id}', 'BotsController@delete')->name('admin.bots.delete');
        });

        Route::group(['prefix' => 'settings'], function () {
            Route::get('/', 'SettingsController@index')->name('admin.settings');
            Route::post('/', 'SettingsController@save');
        });

        Route::group(['prefix' => 'antiminus'], function () {
            Route::get('/', 'AntiminusController@index')->name('admin.antiminus');
            Route::post('/', 'AntiminusController@save');
        });

        Route::group(['prefix' => 'withdraws'], function () {
            Route::get('/', 'WithdrawsController@index')->name('admin.withdraws');
            Route::post('/get', 'WithdrawsController@getById');
            Route::post('/send', 'WithdrawsController@send');
            Route::post('/payout', 'WithdrawsController@payout');

            Route::post('/acceptParadiseSbpPayout', 'WithdrawsController@acceptParadiseSbpPayout');
            Route::post('/acceptCryptobotPayout', [WithdrawsController::class, 'sendToProvider']);
            Route::post('/acceptFKPayout', [WithdrawsController::class, 'sendToProvider']);

            Route::post('/sendWaiting', 'WithdrawsController@waitingSend');
            Route::post('/decline', 'WithdrawsController@decline');
        });

        Route::group(['prefix' => 'deposits'], function () {
            Route::get('/', 'DepositsController@index')->name('admin.deposits');
        });

        Route::group(['prefix' => 'bonus'], function () {
            Route::get('/', 'BonusController@index')->name('admin.bonus');
            Route::post('/', 'BonusController@create')->name('admin.bonus.create');
            Route::get('/delete/{id}', 'BonusController@delete');
        });

        Route::post('/getMerchant', 'IndexController@getMerchant');
    });
});

// Роут для проверки пароля техработ (должен быть ПЕРЕД catch-all роутом)
Route::post('/maintenance/verify', function (\Illuminate\Http\Request $request) {
    $password = $request->input('password');
    $correctPassword = 'uMc4nBT';
    
    if ($password === $correctPassword) {
        // Сохраняем доступ в сессии
        session(['maintenance_access' => true]);
        
        // Также устанавливаем cookie на 24 часа (1440 минут)
        $cookie = cookie('maintenance_access', hash('sha256', $correctPassword), 1440);
        
        return redirect('/')->withCookie($cookie);
    }
    
    // Неверный пароль - возвращаем обратно с ошибкой
    return redirect('/?error=1');
})->name('maintenance.verify');

// Отдельная страница выхода из игры (чтобы избежать рекурсии сайта внутри iframe)
Route::get('/game-exit', function () {
    return view('game-exit');
});

Route::any('/{any}', 'IndexController@index')->where('any', '.*')->name('index');