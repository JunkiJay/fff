@extends('admin/layout')

@section('content')
    <script type="text/javascript" src="/dash/js/chart.min.js"></script>
    <div class="kt-subheader kt-grid__item" id="kt_subheader">
        <div class="kt-subheader__main">
            <h3 class="kt-subheader__title">Статистика</h3>
        </div>
    </div>

    <div class="kt-content  kt-grid__item kt-grid__item--fluid" id="kt_content">
        <div class="kt-portlet">
            <div class="kt-portlet__body  kt-portlet__body--fit">
                <div class="row row-no-padding row-col-separator-xl">

                    <div class="col-md-12 col-lg-6 col-xl-3">
                        <!--begin::Total Profit-->
                        <div class="kt-widget24">
                            <div class="kt-widget24__details">
                                <div class="kt-widget24__info">
                                    <h4 class="kt-widget24__title">
                                        Пополнений на
                                    </h4>
                                    <span class="kt-widget24__desc">
                                    за сегодня
                                </span>
                                </div>

                                <span class="kt-widget24__stats kt-font-success">
                                {{ \App\Models\Payment::query()->whereMonth('created_at', '=', date('m'))->where([['created_at', '>=', \Carbon\Carbon::today()], ['status', 1]])->sum('sum')  }}
                                <i class="la la-rub"></i>
                            </span>
                            </div>
                        </div>
                        <!--end::Total Profit-->
                    </div>

                    <div class="col-md-12 col-lg-6 col-xl-3">
                        <!--begin::New Feedbacks-->
                        <div class="kt-widget24">
                            <div class="kt-widget24__details">
                                <div class="kt-widget24__info">
                                    <h4 class="kt-widget24__title">
                                        Пополнений на
                                    </h4>
                                    <span class="kt-widget24__desc">
                                    за 7 дней
                                </span>
                                </div>

                                <span class="kt-widget24__stats kt-font-success">
                                {{ \App\Models\Payment::query()->whereMonth('created_at', '=', date('m'))->where([['created_at', '>=', \Carbon\Carbon::today()->subDays(7)], ['status', 1]])->sum('sum')  }}<i
                                            class="la la-rub"></i>
                            </span>
                            </div>
                        </div>
                        <!--end::New Feedbacks-->
                    </div>

                    <div class="col-md-12 col-lg-6 col-xl-3">
                        <!--begin::New Orders-->
                        <div class="kt-widget24">
                            <div class="kt-widget24__details">
                                <div class="kt-widget24__info">
                                    <h4 class="kt-widget24__title">
                                        Пополнений на
                                    </h4>
                                    <span class="kt-widget24__desc">
                                    за месяц
                                </span>
                                </div>

                                <span class="kt-widget24__stats kt-font-success">
                                {{ \App\Models\Payment::query()->whereMonth('created_at', '=', date('m'))->whereYear('created_at', '=', date('Y'))->where('status', 1)->sum('sum')  }}<i
                                            class="la la-rub"></i>
                            </span>
                            </div>
                        </div>
                        <!--end::New Orders-->
                    </div>

                    <div class="col-md-12 col-lg-6 col-xl-3">
                        <!--begin::New Users-->
                        <div class="kt-widget24">
                            <div class="kt-widget24__details">
                                <div class="kt-widget24__info">
                                    <h4 class="kt-widget24__title">
                                        Пополнений на
                                    </h4>
                                    <span class="kt-widget24__desc">
                                    за все время
                                </span>
                                </div>

                                <span class="kt-widget24__stats kt-font-success">
                                {{ \App\Models\Payment::query()->where('status', 1)->sum('sum')  }}<i
                                            class="la la-rub"></i>
                            </span>
                            </div>
                        </div>
                        <!--end::New Users-->
                    </div>

                </div>
            </div>
        </div>
        <div class="kt-portlet">
            <div class="kt-portlet__body  kt-portlet__body--fit">
                <div class="row row-no-padding row-col-separator-xl">

                    <div class="col-md-12 col-lg-6 col-xl-3">
                        <!--begin::Total Profit-->
                        <div class="kt-widget24">
                            <div class="kt-widget24__details">
                                <div class="kt-widget24__info">
                                    <h4 class="kt-widget24__title">
                                        Выплат на
                                    </h4>
                                    <span class="kt-widget24__desc">
                                    за сегодня
                                </span>
                                </div>

                                <span class="kt-widget24__stats kt-font-success">
                            {{
                                round(\App\Models\Withdraw::query()
                                    ->whereMonth('created_at', '=', date('m'))
                                    ->where([['fake', 0], ['status', 1]])
                                    ->whereDate('created_at', '=', \Carbon\Carbon::today())
                                    ->selectRaw('SUM(sum) as total_sum')
                                    ->value('total_sum'), 2)
                            }}


                                <i class="la la-rub"></i>
                            </span>
                            </div>
                        </div>
                        <!--end::Total Profit-->
                    </div>

                    <div class="col-md-12 col-lg-6 col-xl-3">
                        <!--begin::New Feedbacks-->
                        <div class="kt-widget24">
                            <div class="kt-widget24__details">
                                <div class="kt-widget24__info">
                                    <h4 class="kt-widget24__title">
                                        Выплат на
                                    </h4>
                                    <span class="kt-widget24__desc">
                                    за 7 дней
                                </span>
                                </div>

                                <span class="kt-widget24__stats kt-font-success">
                            {{
                                round(\App\Models\Withdraw::query()
                                    ->whereMonth('created_at', '=', date('m'))
                                    ->where([['fake', 0], ['status', 1]])
                                    ->where('created_at', '>=', \Carbon\Carbon::today()->subDays(7))
                                    ->selectRaw('SUM(sum) as total_sum')
                                    ->value('total_sum'), 2)
                            }}
                                <i class="la la-rub"></i>
                            </span>
                            </div>
                        </div>
                        <!--end::New Feedbacks-->
                    </div>

                    <div class="col-md-12 col-lg-6 col-xl-3">
                        <!--begin::New Orders-->
                        <div class="kt-widget24">
                            <div class="kt-widget24__details">
                                <div class="kt-widget24__info">
                                    <h4 class="kt-widget24__title">
                                        Выплат на
                                    </h4>
                                    <span class="kt-widget24__desc">
                                    за месяц
                                </span>
                                </div>

                                <span class="kt-widget24__stats kt-font-success">
                            {{
                                round(\App\Models\Withdraw::query()
                                    ->whereMonth('created_at', '=', date('m'))
                                    ->whereYear('created_at', '=', date('Y'))
                                    ->where([['fake', 0], ['status', 1]])
                                    ->selectRaw('SUM(sum) as total_sum')
                                    ->value('total_sum'), 2)
                            }}
                                <i class="la la-rub"></i>
                            </span>
                            </div>
                        </div>
                        <!--end::New Orders-->
                    </div>

                    <div class="col-md-12 col-lg-6 col-xl-3">
                        <!--begin::New Users-->
                        <div class="kt-widget24">
                            <div class="kt-widget24__details">
                                <div class="kt-widget24__info">
                                    <h4 class="kt-widget24__title">
                                        Выплат на
                                    </h4>
                                    <span class="kt-widget24__desc">
                                    за все время
                                </span>
                                </div>

                                <span class="kt-widget24__stats kt-font-success">
                            {{ 
                                round(\App\Models\Withdraw::query()
                                    ->where([
                                        ['fake', 0], 
                                        ['status', 1]
                                    ])
                                    ->selectRaw('SUM(sum) as total_sum')
                                    ->value('total_sum') ?? 0, 2) 
                            }}

                                  <i class="la la-rub"></i>
                            </span>
                            </div>
                        </div>
                        <!--end::New Users-->
                    </div>

                </div>
            </div>
        </div>
        <div class="kt-portlet">
            <div class="kt-portlet__body  kt-portlet__body--fit">
                <div class="row row-no-padding row-col-separator-xl">

                    <div class="col-md-12 col-lg-6 col-xl-4">
                        <!--begin::Total Profit-->
                        <div class="kt-widget24">
                            <div class="kt-widget24__details">
                                <div class="kt-widget24__info">
                                    <h4 class="kt-widget24__title">
                                        Пользователей
                                    </h4>
                                    <span class="kt-widget24__desc">
                                    всего
                                </span>
                                </div>

                                <span class="kt-widget24__stats kt-font-brand">
                                {{ \App\Models\User::query()->count('id')  }}<i class="la la-user"></i>
                            </span>
                            </div>
                        </div>
                        <!--end::Total Profit-->
                    </div>

                    <div class="col-md-12 col-lg-6 col-xl-4">
                        <!--begin::New Orders-->
                        <div class="kt-widget24">
                            <div class="kt-widget24__details">
                                <div class="kt-widget24__info">
                                    <h4 class="kt-widget24__title">
                                        Баланс мерчанта
                                    </h4>
                                    <span class="kt-widget24__desc">FK Wallet RUB</span><br>
                                    <span class="kt-widget24__desc">Cryptobot Wallet USDT</span>
                                </div>

                                <div class="kt-widget24__info">
                                    <b><span style="font-size: 14px; color: #ffb822">{{ $fkWaletBalanceRub }}<i class="la la-rub"></i></span></b><br>
                                    <b><span style="font-size: 14px; color: #ffb822">{{ $cryptobotWaletBalanceUSDT }}<i class="la la-usd"></span></b></i>
                                </div>
                            </div>
                        </div>
                        <!--end::New Orders-->
                    </div>
                    <div class="col-md-12 col-lg-6 col-xl-4">
                        <!--begin::New Users-->
                        <div class="kt-widget24">
                            <div class="kt-widget24__details">
                                <div class="kt-widget24__info">
                                    <h4 class="kt-widget24__title">
                                        Баланс мерчанта
                                    </h4>
                                    <span class="kt-widget24__desc">
                                    FreeKassa RUB
                                </span>
                                </div>

                                <span class="kt-widget24__stats kt-font-warning">
                                <span id="fkBal"><img src="https://media.tenor.com/On7kvXhzml4AAAAj/loading-gif.gif"
                                                      height="26px"></span><i class="la la-rub"></i>
                            </span>
                            </div>
                        </div>
                        <!--end::New Users-->
                    </div>
                </div>
            </div>
        </div>
        <div class="kt-portlet">
            <div class="kt-portlet__body kt-portlet__body--fit">
                <div class="row row-no-padding row-col-separator-xl">

                    <div class="col-md-12 col-lg-12 col-xl-12">
                        <!--begin:: Widgets/Stats2-1 -->
                        <div class="kt-widget1">
                            <div class="kt-widget1__item">
                                <div class="kt-widget1__info">
                                    <h3 class="kt-widget1__title">Промокодов активировано на </h3>
                                </div>
                                <span class="kt-widget1__number {{ ($earnedPromo >= 0) ? 'kt-font-success' : 'kt-font-danger' }}">{{ round($earnedPromo, 2) }}<i
                                            class="la la-rub"></i></span>
                            </div>
                        </div>
                        <!--end:: Widgets/Stats2-1 -->
                    </div>

                    <div class="col-md-12 col-lg-12 col-xl-6">
                        <!--begin:: Widgets/Stats2-1 -->
                        <div class="kt-widget1">
                            <div class="kt-widget1__item">
                                <div class="kt-widget1__info">
                                    <h3 class="kt-widget1__title">Кешбек</h3>
                                </div>
                                <span class="kt-widget1__number {{ ($earnedCashback >= 0) ? 'kt-font-success' : 'kt-font-danger' }}">{{ round($earnedCashback, 2) }}<i
                                            class="la la-rub"></i></span>
                            </div>
                        </div>
                        <!--end:: Widgets/Stats2-1 -->
                    </div>

                    <div class="col-md-12 col-lg-12 col-xl-6">
                        <!--begin:: Widgets/Stats2-1 -->
                        <div class="kt-widget1">
                            <div class="kt-widget1__item">
                                <div class="kt-widget1__info">
                                    <h3 class="kt-widget1__title">Одноразовый бонус</h3>
                                </div>
                                <span class="kt-widget1__number {{ ($earnedOneTime >= 0) ? 'kt-font-success' : 'kt-font-danger' }}">{{ round($earnedOneTime, 2) }}<i
                                            class="la la-rub"></i></span>
                            </div>
                        </div>
                        <!--end:: Widgets/Stats2-1 -->
                    </div>

                    <div class="col-md-12 col-lg-12 col-xl-6">
                        <!--begin:: Widgets/Stats2-1 -->
                        <div class="kt-widget1">
                            <div class="kt-widget1__item">
                                <div class="kt-widget1__info">
                                    <h3 class="kt-widget1__title">Бонус за репост</h3>
                                </div>
                                <span class="kt-widget1__number {{ ($earnedRepost >= 0) ? 'kt-font-success' : 'kt-font-danger' }}">{{ round($earnedRepost, 2) }}<i
                                            class="la la-rub"></i></span>
                            </div>
                        </div>
                        <!--end:: Widgets/Stats2-1 -->
                    </div>

                    <div class="col-md-12 col-lg-12 col-xl-6">
                        <!--begin:: Widgets/Stats2-1 -->
                        <div class="kt-widget1">
                            <div class="kt-widget1__item">
                                <div class="kt-widget1__info">
                                    <h3 class="kt-widget1__title">Бонусы реферальной системы</h3>
                                </div>
                                <span class="kt-widget1__number {{ ($earnedRef >= 0) ? 'kt-font-success' : 'kt-font-danger' }}">{{ round($earnedRef, 2) }}<i
                                            class="la la-rub"></i></span>
                            </div>
                        </div>
                        <!--end:: Widgets/Stats2-1 -->
                    </div>

                    <div class="col-md-12 col-lg-12 col-xl-6">
                        <!--begin:: Widgets/Stats2-1 -->
                        <div class="kt-widget1">
                            <div class="kt-widget1__item">
                                <div class="kt-widget1__info">
                                    <h3 class="kt-widget1__title">Ставок Dice</h3>
                                </div>
                                <span class="kt-widget1__number {{ ($amountDice >= 0) ? 'kt-font-success' : 'kt-font-danger' }}">{{ round($amountDice, 2) }}<i
                                            class="la la-rub"></i></span>
                            </div>
                        </div>
                        <!--end:: Widgets/Stats2-1 -->
                    </div>
                    <div class="col-md-12 col-lg-12 col-xl-6">
                        <!--begin:: Widgets/Stats2-1 -->
                        <div class="kt-widget1">
                            <div class="kt-widget1__item">
                                <div class="kt-widget1__info">
                                    <h3 class="kt-widget1__title">Выигрышей Dice</h3>
                                </div>
                                <span class="kt-widget1__number {{ ($profitDice >= 0) ? 'kt-font-success' : 'kt-font-danger' }}">{{ round($profitDice, 2) }}<i
                                            class="la la-rub"></i></span>
                            </div>
                        </div>
                        <!--end:: Widgets/Stats2-1 -->
                    </div>
                    <div class="col-md-12 col-lg-12 col-xl-6">
                        <!--begin:: Widgets/Stats2-1 -->
                        <div class="kt-widget1">
                            <div class="kt-widget1__item">
                                <div class="kt-widget1__info">
                                    <h3 class="kt-widget1__title">Ставок Mines</h3>
                                </div>
                                <span class="kt-widget1__number {{ ($amountMines >= 0) ? 'kt-font-success' : 'kt-font-danger' }}">{{ round($amountMines, 2) }}<i
                                            class="la la-rub"></i></span>
                            </div>
                        </div>
                        <!--end:: Widgets/Stats2-1 -->
                    </div>
                    <div class="col-md-12 col-lg-12 col-xl-6">
                        <!--begin:: Widgets/Stats2-1 -->
                        <div class="kt-widget1">
                            <div class="kt-widget1__item">
                                <div class="kt-widget1__info">
                                    <h3 class="kt-widget1__title">Выигрышей Mines</h3>
                                </div>
                                <span class="kt-widget1__number {{ ($profitMines >= 0) ? 'kt-font-success' : 'kt-font-danger' }}">{{ round($profitMines, 2) }}<i
                                            class="la la-rub"></i></span>
                            </div>
                        </div>
                        <!--end:: Widgets/Stats2-1 -->
                    </div>
                    <div class="col-md-12 col-lg-12 col-xl-6">
                        <!--begin:: Widgets/Stats2-1 -->
                        <div class="kt-widget1">
                            <div class="kt-widget1__item">
                                <div class="kt-widget1__info">
                                    <h3 class="kt-widget1__title">Ставок Bubbles</h3>
                                </div>
                                <span class="kt-widget1__number {{ ($amountBubbles >= 0) ? 'kt-font-success' : 'kt-font-danger' }}">{{ round($amountBubbles, 2) }}<i
                                            class="la la-rub"></i></span>
                            </div>
                        </div>
                        <!--end:: Widgets/Stats2-1 -->
                    </div>
                    <div class="col-md-12 col-lg-12 col-xl-6">
                        <!--begin:: Widgets/Stats2-1 -->
                        <div class="kt-widget1">
                            <div class="kt-widget1__item">
                                <div class="kt-widget1__info">
                                    <h3 class="kt-widget1__title">Выигрышей Bubbles</h3>
                                </div>
                                <span class="kt-widget1__number {{ ($profitBubbles >= 0) ? 'kt-font-success' : 'kt-font-danger' }}">{{ round($profitBubbles, 2) }}<i
                                            class="la la-rub"></i></span>
                            </div>
                        </div>
                        <!--end:: Widgets/Stats2-1 -->
                    </div>
                    <div class="col-md-12 col-lg-12 col-xl-6">
                        <!--begin:: Widgets/Stats2-1 -->
                        <div class="kt-widget1">
                            <div class="kt-widget1__item">
                                <div class="kt-widget1__info">
                                    <h3 class="kt-widget1__title">Ставок Plinko</h3>
                                </div>
                                <span class="kt-widget1__number {{ ($amountPlinko >= 0) ? 'kt-font-success' : 'kt-font-danger' }}">{{ round($amountPlinko, 2) }}<i
                                            class="la la-rub"></i></span>
                            </div>
                        </div>
                        <!--end:: Widgets/Stats2-1 -->
                    </div>
                    <div class="col-md-12 col-lg-12 col-xl-6">
                        <!--begin:: Widgets/Stats2-1 -->
                        <div class="kt-widget1">
                            <div class="kt-widget1__item">
                                <div class="kt-widget1__info">
                                    <h3 class="kt-widget1__title">Выигрышей Plinko</h3>
                                </div>
                                <span class="kt-widget1__number {{ ($profitPlinko >= 0) ? 'kt-font-success' : 'kt-font-danger' }}">{{ round($profitPlinko, 2) }}<i
                                            class="la la-rub"></i></span>
                            </div>
                        </div>
                        <!--end:: Widgets/Stats2-1 -->
                    </div>
                    <div class="col-md-12 col-lg-12 col-xl-6">
                        <!--begin:: Widgets/Stats2-1 -->
                        <div class="kt-widget1">
                            <div class="kt-widget1__item">
                                <div class="kt-widget1__info">
                                    <h3 class="kt-widget1__title">Ставок Wheel</h3>
                                </div>
                                <span class="kt-widget1__number {{ ($amountWheel >= 0) ? 'kt-font-success' : 'kt-font-danger' }}">{{ round($amountWheel, 2) }}<i
                                            class="la la-rub"></i></span>
                            </div>
                        </div>
                        <!--end:: Widgets/Stats2-1 -->
                    </div>
                    <div class="col-md-12 col-lg-12 col-xl-6">
                        <!--begin:: Widgets/Stats2-1 -->
                        <div class="kt-widget1">
                            <div class="kt-widget1__item">
                                <div class="kt-widget1__info">
                                    <h3 class="kt-widget1__title">Выигрышей Wheel</h3>
                                </div>
                                <span class="kt-widget1__number {{ ($profitWheel >= 0) ? 'kt-font-success' : 'kt-font-danger' }}">{{ round($profitWheel, 2) }}<i
                                            class="la la-rub"></i></span>
                            </div>
                        </div>
                        <!--end:: Widgets/Stats2-1 -->
                    </div>
                </div>
                <div class="row row-no-padding row-col-separator-xl">
                    <div class="col-md-12 col-lg-12 col-xl-6">
                        <!--begin:: Widgets/Stats2-1 -->
                        <div class="kt-widget1">
                            <div class="kt-widget1__item">
                                <div class="kt-widget1__info">
                                    <h3 class="kt-widget1__title">Общие ставки</h3>
                                </div>
                                <span class="kt-widget1__number {{ ($amountDice + $amountMines + $amountBubbles + $amountPlinko + $amountWheel >= 0) ? 'kt-font-success' : 'kt-font-danger' }}">{{ round($amountDice + $amountMines + $amountBubbles + $amountPlinko + $amountWheel , 2) }}<i
                                            class="la la-rub"></i></span>
                            </div>
                        </div>
                        <!--end:: Widgets/Stats2-1 -->
                    </div>
                    <div class="col-md-12 col-lg-12 col-xl-6">
                        <!--begin:: Widgets/Stats2-1 -->
                        <div class="kt-widget1">
                            <div class="kt-widget1__item">
                                <div class="kt-widget1__info">
                                    <h3 class="kt-widget1__title">Общий профит</h3>
                                </div>
                                <span class="kt-widget1__number {{ ($profitDice + $profitMines + $profitBubbles + $profitPlinko + $profitWheel >= 0) ? 'kt-font-success' : 'kt-font-danger' }}">{{ round($profitDice + $profitMines + $profitBubbles + $profitPlinko + $profitWheel , 2) }}<i
                                            class="la la-rub"></i></span>
                            </div>
                        </div>
                        <!--end:: Widgets/Stats2-1 -->
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-xl-6">
                <div class="kt-portlet kt-portlet--height-fluid">
                    <div class="kt-portlet__head">
                        <div class="kt-portlet__head-label">
                            <h3 class="kt-portlet__head-title">
                                График регистраций за текущий месяц
                            </h3>
                        </div>
                    </div>
                    <div class="kt-portlet__body kt-portlet__body--fluid">
                        <div class="kt-widget12">
                            <div class="kt-widget12__chart" style="height:250px;">
                                <canvas id="userStatsChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- <div class="col-xl-6">
                <div class="kt-portlet kt-portlet--height-fluid">
                    <div class="kt-portlet__head">
                        <div class="kt-portlet__head-label">
                            <h3 class="kt-portlet__head-title">
                                График пополнений за текущий месяц
                            </h3>
                        </div>
                    </div>
                    <div class="kt-portlet__body kt-portlet__body--fluid">
                        <div class="kt-widget12">
                            <div class="kt-widget12__chart" style="height:250px;">
                                <canvas id="depsChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-6">
                <div class="kt-portlet kt-portlet--height-fluid">
                    <div class="kt-portlet__head">
                        <div class="kt-portlet__head-label">
                            <h3 class="kt-portlet__head-title">
                                График прибыли за текущий месяц
                            </h3>
                        </div>
                    </div>
                    <div class="kt-portlet__body kt-portlet__body--fluid">
                        <div class="kt-widget12">
                            <div class="kt-widget12__chart" style="height:250px;">
                                <canvas id="ProfitChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-6">
                <div class="kt-portlet kt-portlet--height-fluid">
                    <div class="kt-portlet__head">
                        <div class="kt-portlet__head-label">
                            <h3 class="kt-portlet__head-title">
                                График выводов за текущий месяц
                            </h3>
                        </div>
                    </div>
                    <div class="kt-portlet__body kt-portlet__body--fluid">
                        <div class="kt-widget12">
                            <div class="kt-widget12__chart" style="height:250px;">
                                <canvas id="withdrawChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div> -->

            <div class="col-xl-6">
                <div class="kt-portlet kt-portlet--height-fluid">
                    <div class="kt-portlet__head">
                        <div class="kt-portlet__head-label">
                            <h3 class="kt-portlet__head-title">
                                График выводов за текущий месяц
                            </h3>
                        </div>
                    </div>
                    <div class="kt-portlet__body kt-portlet__body--fluid">
                        <div class="kt-widget12">
                            <div class="kt-widget12__chart" style="height:250px;">
                                <canvas id="statsChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


        </div>
    </div>

    <script>
        $(document).ready(function() {
            // $.ajax({
            //     method: 'POST',
            //     url: '/admin/getUserByMonth',
            //     success: function(res) {
            //         var authChart = 'authChart';
            //         if ($('#' + authChart).length > 0) {
            //             var months = [];
            //             var users = [];

            //             $.each(res, function(index, data) {
            //                 months.push(data.date);
            //                 users.push(data.count);
            //             });

            //             var lineCh = document.getElementById(authChart).getContext("2d");

            //             var chart = new Chart(lineCh, {
            //                 type: 'line',
            //                 data: {
            //                     labels: months,
            //                     datasets: [{
            //                         label: "",
            //                         tension: 0.4,
            //                         backgroundColor: 'transparent',
            //                         borderColor: '#2c80ff',
            //                         pointBorderColor: "#2c80ff",
            //                         pointBackgroundColor: "#fff",
            //                         pointBorderWidth: 2,
            //                         pointHoverRadius: 6,
            //                         pointHoverBackgroundColor: "#fff",
            //                         pointHoverBorderColor: "#2c80ff",
            //                         pointHoverBorderWidth: 2,
            //                         pointRadius: 6,
            //                         pointHitRadius: 6,
            //                         data: users,
            //                     }]
            //                 },
            //                 options: {
            //                     legend: {
            //                         display: false
            //                     },
            //                     maintainAspectRatio: false,
            //                     tooltips: {
            //                         callbacks: {
            //                             title: function(tooltipItem, data) {
            //                                 return 'Дата : ' + data['labels'][tooltipItem[0]['index']];
            //                             },
            //                             label: function(tooltipItem, data) {
            //                                 return data['datasets'][0]['data'][tooltipItem['index']] + ' чел.';
            //                             }
            //                         },
            //                         backgroundColor: '#eff6ff',
            //                         titleFontSize: 13,
            //                         titleFontColor: '#6783b8',
            //                         titleMarginBottom: 10,
            //                         bodyFontColor: '#9eaecf',
            //                         bodyFontSize: 14,
            //                         bodySpacing: 4,
            //                         yPadding: 15,
            //                         xPadding: 15,
            //                         footerMarginTop: 5,
            //                         displayColors: false
            //                     },
            //                     scales: {
            //                         yAxes: [{
            //                             ticks: {
            //                                 beginAtZero: true,
            //                                 fontSize: 12,
            //                                 fontColor: '#9eaecf',
            //                                 stepSize: Math.ceil(users / 5)
            //                             },
            //                             gridLines: {
            //                                 color: "#e5ecf8",
            //                                 tickMarkLength: 0,
            //                                 zeroLineColor: '#e5ecf8'
            //                             },

            //                         }],
            //                         xAxes: [{
            //                             ticks: {
            //                                 beginAtZero: true,
            //                                 fontSize: 12,
            //                                 fontColor: '#9eaecf',
            //                                 source: 'auto',
            //                             },
            //                             gridLines: {
            //                                 color: "transparent",
            //                                 tickMarkLength: 20,
            //                                 zeroLineColor: '#e5ecf8',
            //                             },
            //                         }]
            //                     }
            //                 }
            //             });
            //         }
            //     }
            // });
            // $.ajax({
            //     method: 'POST',
            //     url: '/admin/getDepsByMonth',
            //     success: function(res) {
            //         var depsChart = 'depsChart';
            //         if ($('#' + depsChart).length > 0) {
            //             var months = [];
            //             var deps = [];

            //             $.each(res, function(index, data) {
            //                 months.push(data.date);
            //                 deps.push(data.sum);
            //             });

            //             var lineCh = document.getElementById(depsChart).getContext("2d");

            //             var chart = new Chart(lineCh, {
            //                 type: 'line',
            //                 data: {
            //                     labels: months,
            //                     datasets: [{
            //                         label: "",
            //                         tension: 0.4,
            //                         backgroundColor: 'transparent',
            //                         borderColor: '#2c80ff',
            //                         pointBorderColor: "#2c80ff",
            //                         pointBackgroundColor: "#fff",
            //                         pointBorderWidth: 2,
            //                         pointHoverRadius: 6,
            //                         pointHoverBackgroundColor: "#fff",
            //                         pointHoverBorderColor: "#2c80ff",
            //                         pointHoverBorderWidth: 2,
            //                         pointRadius: 6,
            //                         pointHitRadius: 6,
            //                         data: deps,
            //                     }]
            //                 },
            //                 options: {
            //                     legend: {
            //                         display: false
            //                     },
            //                     maintainAspectRatio: false,
            //                     tooltips: {
            //                         callbacks: {
            //                             title: function(tooltipItem, data) {
            //                                 return 'Дата : ' + data['labels'][tooltipItem[0]['index']];
            //                             },
            //                             label: function(tooltipItem, data) {
            //                                 return data['datasets'][0]['data'][tooltipItem['index']] + ' руб.';
            //                             }
            //                         },
            //                         backgroundColor: '#eff6ff',
            //                         titleFontSize: 13,
            //                         titleFontColor: '#6783b8',
            //                         titleMarginBottom: 10,
            //                         bodyFontColor: '#9eaecf',
            //                         bodyFontSize: 14,
            //                         bodySpacing: 4,
            //                         yPadding: 15,
            //                         xPadding: 15,
            //                         footerMarginTop: 5,
            //                         displayColors: false
            //                     },
            //                     scales: {
            //                         yAxes: [{
            //                             ticks: {
            //                                 beginAtZero: true,
            //                                 fontSize: 12,
            //                                 fontColor: '#9eaecf',
            //                                 stepSize: Math.ceil(deps / 5)
            //                             },
            //                             gridLines: {
            //                                 color: "#e5ecf8",
            //                                 tickMarkLength: 0,
            //                                 zeroLineColor: '#e5ecf8'
            //                             },

            //                         }],
            //                         xAxes: [{
            //                             ticks: {
            //                                 fontSize: 12,
            //                                 fontColor: '#9eaecf',
            //                                 source: 'auto',
            //                             },
            //                             gridLines: {
            //                                 color: "transparent",
            //                                 tickMarkLength: 20,
            //                                 zeroLineColor: '#e5ecf8',
            //                             },
            //                         }]
            //                     }
            //                 }
            //             });
            //         }
            //     }
            // });
            // $.ajax({
            //     method: 'POST',
            //     url: '/admin/getProfitByMonth',
            //     success: function(res) {
            //         var ProfitChart = 'ProfitChart';
            //         if ($('#' + ProfitChart).length > 0) {
            //             var months = [];
            //             var deps = [];

            //             $.each(res, function(index, data) {
            //                 months.push(data.date);
            //                 deps.push(data.sum);
            //             });

            //             var lineCh = document.getElementById(ProfitChart).getContext("2d");

            //             var chart = new Chart(lineCh, {
            //                 type: 'line',
            //                 data: {
            //                     labels: months,
            //                     datasets: [{
            //                         label: "",
            //                         tension: 0.4,
            //                         backgroundColor: 'transparent',
            //                         borderColor: '#2c80ff',
            //                         pointBorderColor: "#2c80ff",
            //                         pointBackgroundColor: "#fff",
            //                         pointBorderWidth: 2,
            //                         pointHoverRadius: 6,
            //                         pointHoverBackgroundColor: "#fff",
            //                         pointHoverBorderColor: "#2c80ff",
            //                         pointHoverBorderWidth: 2,
            //                         pointRadius: 6,
            //                         pointHitRadius: 6,
            //                         data: deps,
            //                     }]
            //                 },
            //                 options: {
            //                     legend: {
            //                         display: false
            //                     },
            //                     maintainAspectRatio: false,
            //                     tooltips: {
            //                         callbacks: {
            //                             title: function(tooltipItem, data) {
            //                                 return 'Дата : ' + data['labels'][tooltipItem[0]['index']];
            //                             },
            //                             label: function(tooltipItem, data) {
            //                                 return data['datasets'][0]['data'][tooltipItem['index']] + ' руб.';
            //                             }
            //                         },
            //                         backgroundColor: '#eff6ff',
            //                         titleFontSize: 13,
            //                         titleFontColor: '#6783b8',
            //                         titleMarginBottom: 10,
            //                         bodyFontColor: '#9eaecf',
            //                         bodyFontSize: 14,
            //                         bodySpacing: 4,
            //                         yPadding: 15,
            //                         xPadding: 15,
            //                         footerMarginTop: 5,
            //                         displayColors: false
            //                     },
            //                     scales: {
            //                         yAxes: [{
            //                             ticks: {
            //                                 beginAtZero: true,
            //                                 fontSize: 12,
            //                                 fontColor: '#9eaecf',
            //                                 stepSize: Math.ceil(deps / 5)
            //                             },
            //                             gridLines: {
            //                                 color: "#e5ecf8",
            //                                 tickMarkLength: 0,
            //                                 zeroLineColor: '#e5ecf8'
            //                             },

            //                         }],
            //                         xAxes: [{
            //                             ticks: {
            //                                 fontSize: 12,
            //                                 fontColor: '#9eaecf',
            //                                 source: 'auto',
            //                             },
            //                             gridLines: {
            //                                 color: "transparent",
            //                                 tickMarkLength: 20,
            //                                 zeroLineColor: '#e5ecf8',
            //                             },
            //                         }]
            //                     }
            //                 }
            //             });
            //         }
            //     }
            // });
            // $.ajax({
            //     method: 'POST',
            //     url: '/admin/getWithdrawByMonth',
            //     success: function(res) {
            //         var withdrawChart = 'withdrawChart';
            //         if ($('#' + withdrawChart).length > 0) {
            //             var months = [];
            //             var deps = [];

            //             $.each(res, function(index, data) {
            //                 months.push(data.date);
            //                 deps.push(data.sum);
            //             });

            //             var lineCh = document.getElementById(withdrawChart).getContext("2d");

            //             var chart = new Chart(lineCh, {
            //                 type: 'line',
            //                 data: {
            //                     labels: months,
            //                     datasets: [{
            //                         label: "",
            //                         tension: 0.4,
            //                         backgroundColor: 'transparent',
            //                         borderColor: '#2c80ff',
            //                         pointBorderColor: "#2c80ff",
            //                         pointBackgroundColor: "#fff",
            //                         pointBorderWidth: 2,
            //                         pointHoverRadius: 6,
            //                         pointHoverBackgroundColor: "#fff",
            //                         pointHoverBorderColor: "#2c80ff",
            //                         pointHoverBorderWidth: 2,
            //                         pointRadius: 6,
            //                         pointHitRadius: 6,
            //                         data: deps,
            //                     }]
            //                 },
            //                 options: {
            //                     legend: {
            //                         display: false
            //                     },
            //                     maintainAspectRatio: false,
            //                     tooltips: {
            //                         callbacks: {
            //                             title: function(tooltipItem, data) {
            //                                 return 'Дата : ' + data['labels'][tooltipItem[0]['index']];
            //                             },
            //                             label: function(tooltipItem, data) {
            //                                 return data['datasets'][0]['data'][tooltipItem['index']] + ' руб.';
            //                             }
            //                         },
            //                         backgroundColor: '#eff6ff',
            //                         titleFontSize: 13,
            //                         titleFontColor: '#6783b8',
            //                         titleMarginBottom: 10,
            //                         bodyFontColor: '#9eaecf',
            //                         bodyFontSize: 14,
            //                         bodySpacing: 4,
            //                         yPadding: 15,
            //                         xPadding: 15,
            //                         footerMarginTop: 5,
            //                         displayColors: false
            //                     },
            //                     scales: {
            //                         yAxes: [{
            //                             ticks: {
            //                                 beginAtZero: true,
            //                                 fontSize: 12,
            //                                 fontColor: '#9eaecf',
            //                                 stepSize: Math.ceil(deps / 5)
            //                             },
            //                             gridLines: {
            //                                 color: "#e5ecf8",
            //                                 tickMarkLength: 0,
            //                                 zeroLineColor: '#e5ecf8'
            //                             },

            //                         }],
            //                         xAxes: [{
            //                             ticks: {
            //                                 fontSize: 12,
            //                                 fontColor: '#9eaecf',
            //                                 source: 'auto',
            //                             },
            //                             gridLines: {
            //                                 color: "transparent",
            //                                 tickMarkLength: 20,
            //                                 zeroLineColor: '#e5ecf8',
            //                             },
            //                         }]
            //                     }
            //                 }
            //             });
            //         }
            //     }
            // });
            $.ajax({
                method: 'POST',
                url: '/admin/getAllStatsByMonth', // Единый эндпоинт для всех статистик
                success: function(res) {
                    var chartId = 'statsChart'; // Одиночный элемент для графика
                    if ($('#' + chartId).length > 0) {
                        var months = [];
                        var deps = [];
                        var profits = [];
                        var withdraws = [];

                        $.each(res.deps, function(index, data) {
                            months.push(data.date);
                            deps.push(data.sum);
                        });

                        $.each(res.profits, function(index, data) {
                            profits.push(data.sum);
                        });

                        $.each(res.withdraws, function(index, data) {
                            withdraws.push(data.sum);
                        });

                        var lineCh = document.getElementById(chartId).getContext("2d");

                        var chart = new Chart(lineCh, {
                            type: 'line',
                            data: {
                                labels: months,
                                datasets: [{
                                    label: "Депозиты",
                                    tension: 0.4,
                                    backgroundColor: 'transparent',
                                    borderColor: '#2c80ff',
                                    pointBorderColor: "#2c80ff",
                                    pointBackgroundColor: "#fff",
                                    pointRadius: 6,
                                    pointHoverRadius: 8,
                                    data: deps,
                                },
                                    {
                                        label: "Прибыль",
                                        tension: 0.4,
                                        backgroundColor: 'transparent',
                                        borderColor: '#00c851',
                                        pointBorderColor: "#00c851",
                                        pointBackgroundColor: "#fff",
                                        pointRadius: 6,
                                        pointHoverRadius: 8,
                                        data: profits,
                                    },
                                    {
                                        label: "Выводы",
                                        tension: 0.4,
                                        backgroundColor: 'transparent',
                                        borderColor: '#dc3545',
                                        pointBorderColor: "#dc3545",
                                        pointBackgroundColor: "#fff",
                                        pointRadius: 6,
                                        pointHoverRadius: 8,
                                        data: withdraws,
                                    }
                                ]
                            },
                            options: {
                                legend: {
                                    display: true
                                },
                                maintainAspectRatio: false,
                                tooltips: {
                                    callbacks: {
                                        title: function(tooltipItem, data) {
                                            return 'Дата: ' + data['labels'][tooltipItem[0]['index']];
                                        },
                                        label: function(tooltipItem, data) {
                                            return data['datasets'][tooltipItem.datasetIndex]['label'] + ": " +
                                                data['datasets'][tooltipItem.datasetIndex]['data'][tooltipItem.index] + ' руб.';
                                        }
                                    }
                                },
                                scales: {
                                    yAxes: [{
                                        ticks: {
                                            beginAtZero: true
                                        },
                                        gridLines: {
                                            color: "#e5ecf8"
                                        },
                                    }],
                                    xAxes: [{
                                        ticks: {
                                            fontSize: 12,
                                            fontColor: '#9eaecf'
                                        },
                                        gridLines: {
                                            color: "transparent"
                                        },
                                    }]
                                }
                            }
                        });
                    }
                }
            });

            $.ajax({
                method: 'POST',
                url: '/admin/getUserStatsByMonth',
                success: function(res) {
                    var dates = [];
                    var totalUsers = [];
                    var usersWithDeposit = [];
                    var activeUsers = [];

                    res.forEach(data => {
                        dates.push(data.date);
                        totalUsers.push(data.total_users);
                        usersWithDeposit.push(data.users_with_deposit);
                        activeUsers.push(data.active_users);
                    });

                    var ctx = document.getElementById("userStatsChart").getContext("2d");
                    new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: dates,
                            datasets: [{
                                label: "Все пользователи",
                                data: totalUsers,
                                tension: 0.4,
                                borderColor: "#2c80ff",
                                pointBorderColor: "#2c80ff",
                                pointBackgroundColor: "#fff",
                                backgroundColor: "transparent",
                                pointRadius: 6,
                                pointHoverRadius: 8
                            },
                                {
                                    label: "Новые перводепы",
                                    data: usersWithDeposit,
                                    tension: 0.4,
                                    borderColor: "#ff5c5c",
                                    pointBorderColor: "#ff5c5c",
                                    pointBackgroundColor: "#fff",
                                    backgroundColor: "transparent",
                                    pointRadius: 6,
                                    pointHoverRadius: 8
                                },
                                {
                                    label: "Активных игроков",
                                    data: activeUsers,
                                    tension: 0.4,
                                    borderColor: "#00c851",
                                    pointBorderColor: "#00c851",
                                    pointBackgroundColor: "#fff",
                                    backgroundColor: "transparent",
                                    pointRadius: 6,
                                    pointHoverRadius: 8
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                yAxes: [{
                                    ticks: {
                                        beginAtZero: true
                                    }
                                }]
                            }
                        }
                    });
                }
            });


        });
    </script>
@endsection