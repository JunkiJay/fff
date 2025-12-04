@extends('admin/layout')

@section('content')
    <script src="/dash/js/dtables.js?v=1" type="text/javascript"></script>
    <div class="kt-subheader kt-grid__item" id="kt_subheader">
        <div class="kt-subheader__main">
            <h3 class="kt-subheader__title">Пользователи</h3>
        </div>
    </div>

    <div class="kt-content kt-grid__item kt-grid__item--fluid" id="kt_content">
        <div class="kt-portlet kt-portlet--tabs">
            <div class="kt-portlet__head">
                <div class="kt-portlet__head-toolbar">
                    <ul class="nav nav-tabs nav-tabs-line nav-tabs-line-danger nav-tabs-line-2x nav-tabs-line-right"
                        role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-toggle="tab" href="#userSettings" role="tab"
                               aria-selected="true">
                                Даннные пользователя {{ $user->username }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#userStatistics" role="tab"
                               aria-selected="true">
                                Статистика
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#userRoles" role="tab" aria-selected="true">
                                Роли
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#userLogs" role="tab" aria-selected="true">
                                Логи
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            <form method="GET" action="{{ route('admin.users.edit', ['id' => $user->id]) }}" class="form-inline mb-3">
                <div class="form-group mr-2">
                    <label for="start_date">Начальная дата:</label>
                    <input type="date" name="start_date" id="start_date" class="form-control"
                           value="{{ request('start_date') }}">
                </div>
                <div class="form-group mr-2">
                    <label for="end_date">Конечная дата:</label>
                    <input type="date" name="end_date" id="end_date" class="form-control"
                           value="{{ request('end_date') }}">
                </div>
                <button type="submit" class="btn btn-primary">Применить (ref)</button>
            </form>
            <form id="logsFilterForm" class="form-inline mb-3">
                <div class="form-group mr-2" style="display: flex">
                    <label for="log_start_date">Начальная дата:</label>
                    <input type="date" name="log_start_date" id="log_start_date" class="form-control">
                    <label for="log_end_date">Конечная дата:</label>
                    <input type="date" name="log_end_date" id="log_end_date" class="form-control">
                    <button type="button" class="btn btn-primary" id="logsFilterBtn">Применить (logs)</button>
                </div>
            </form>
            <form class="kt-form" method="post" action="/admin/users/edit/{{ $user->id }}">
                <div class="kt-portlet__body">
                    <div class="tab-content">
                        <div class="tab-pane active" id="userSettings" role="tabpanel">
                            <div class="kt-section">
                                <div class="form-group row">
                                    <div class="col-lg-3">
                                        <label>Статус автовывода</label>
                                        <select class="form-control" name="auto_withdraw">
                                            <option value="0" @if(is_null($user->auto_withdraw)) selected @endif>
                                                Активен
                                            </option>
                                            <option value="1" @if($user->auto_withdraw == 1) selected @endif>
                                                Заблокирован
                                            </option>
                                        </select>
                                    </div>
                                    <div class="col-lg-3">
                                        <label>Логин:</label>
                                        <input type="text" autocomplete="off" class="form-control" placeholder=""
                                               value="{{ $user->username }}" name="username" minlength="1" required />
                                    </div>
                                    <div class="col-lg-3">
                                        <label>Баланс (₽):</label>
                                        <input type="text" autocomplete="off" class="form-control" placeholder=""
                                               value="{{ $user->balance }}" name="balance" required />
                                    </div>
                                    <div class="col-lg-3">
                                        <label>Заблокирован?</label>
                                        <select class="form-control" name="ban">
                                            <option value="1" @if($user->ban == 1) selected @endif>Да</option>
                                            <option value="0" @if($user->ban == 0) selected @endif>Нет</option>
                                        </select>
                                    </div>
                                    <div class="col-lg-3">
                                        <label>Ограничить платёжки?</label>
                                        <select class="form-control" name="limit_payment">
                                            <option value="1" @if($user->limit_payment == 1) selected @endif>Да</option>
                                            <option value="0" @if($user->limit_payment == 0) selected @endif>Нет
                                            </option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-lg-4">
                                        <label>Отыгрыш в слотах</label>
                                        <input type="text" autocomplete="off" class="form-control" placeholder=""
                                               value="{{ $user->slots_wager }}" name="slots_wager" required />
                                    </div>
                                    <div class="col-lg-4">
                                        <label>Необходимо отыграть (₽):</label>
                                        <input type="text" autocomplete="off" class="form-control" placeholder=""
                                               value="{{ $user->wager }}" name="wager" required />
                                    </div>
                                    <div class="col-lg-4">
                                        <label>Включена отыгровка?</label>
                                        <select class="form-control" name="wager_status">
                                            <option value="1" @if($user->wager_status == 1) selected @endif>Да</option>
                                            <option value="0" @if($user->wager_status == 0) selected @endif>Нет</option>
                                        </select>
                                    </div>
                                    <div class="col-lg-4">
                                        <label>VK Профиль: <span style="cursor: pointer;" class="kt-font-primary"
                                                                 id="vkDate" onclick="getRegDate({{ $user->vk_id }})">Показать дату</span></label>
                                        <input type="text" autocomplete="off" class="form-control" placeholder=""
                                               value="@if($user->vk_id > 0) https://vk.com/id{{ $user->vk_id }} @else Не привязан @endif"
                                               disabled />
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-lg-4">
                                        <label>% за пополнение реф 1-lvl::</label>
                                        <input type="text" autocomplete="off" class="form-control" placeholder=""
                                               value="{{ $user->ref_1_lvl }}" name="ref_1_lvl" />
                                    </div>
                                    <div class="col-lg-4">
                                        <label>% за пополнение реф 2-lvl::</label>
                                        <input type="text" autocomplete="off" class="form-control" placeholder=""
                                               value="{{ $user->ref_2_lvl }}" name="ref_2_lvl" />
                                    </div>
                                    <div class="col-lg-4">
                                        <label>% за пополнение реф 3-lvl:</label>
                                        <input type="text" autocomplete="off" class="form-control" placeholder=""
                                               value="{{ $user->ref_3_lvl }}" name="ref_3_lvl" />
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-lg-3">
                                        <label>IP: <span style="cursor: pointer;" class="kt-font-primary"
                                                         onclick="getCountry('{{ $user->used_ip }}', this)">Узнать город</span></label>
                                        <input type="text" autocomplete="off" class="form-control" placeholder=""
                                               value="{{ $user->used_ip }}" disabled />
                                    </div>
                                    <div class="col-lg-3">
                                        <label>IP при регистрации: <span style="cursor: pointer;"
                                                                         class="kt-font-primary"
                                                                         onclick="getCountry('{{ $user->created_ip }}', this)">Узнать город</span></label>
                                        <input type="text" autocomplete="off" class="form-control" placeholder=""
                                               value="{{ $user->created_ip }}" disabled />
                                    </div>
                                    <div class="col-lg-3">
                                        <label>Видеокарта:</label>
                                        <input type="text" autocomplete="off" class="form-control" placeholder=""
                                               value="{{ $user->videocard }}" disabled />
                                    </div>
                                    <div class="col-lg-3">
                                        <label>Уникальный отпечаток:</label>
                                        <input type="text" autocomplete="off" class="form-control" placeholder=""
                                               value="{{ $user->fingerprint }}" disabled />
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane" id="userStatistics" role="tabpanel">
                            <div class="kt-section">
                                <div class="form-group row">
                                    <div class="col-lg-4">
                                        <label>Доход партнера:</label>
                                        <input
                                                type="text"
                                                autocomplete="off"
                                                class="form-control"
                                                placeholder=""
                                                value="{{ \App\Models\ReferralProfit::query()->where('ref_id', $user->id)->sum('amount') }}"
                                                disabled />
                                    </div>
                                    <div class="col-lg-4">
                                        <label>Регистрации:</label>
                                        <input
                                                type="text"
                                                autocomplete="off"
                                                class="form-control"
                                                placeholder=""
                                                value="{{ $refData[0] }}"
                                                disabled />
                                    </div>
                                    <div class="col-lg-4">
                                        <label>Кол-во первых депозитов:</label>
                                        <input
                                                type="text"
                                                autocomplete="off"
                                                class="form-control"
                                                placeholder=""
                                                value="{{ $refData[1] }}"
                                                disabled />
                                    </div>

                                    <div class="col-lg-4">
                                        <label>Сумма первых депозитов:</label>
                                        <input
                                                type="text"
                                                autocomplete="off"
                                                class="form-control"
                                                placeholder=""
                                                value="{{ $refData[2] }}"
                                                disabled />
                                    </div>
                                    <div class="col-lg-4">
                                        <label>Кол-во депозитов:</label>
                                        <input
                                                type="text"
                                                autocomplete="off"
                                                class="form-control"
                                                placeholder=""
                                                value="{{ $refData[3] }}"
                                                disabled />
                                    </div>
                                    <div class="col-lg-4">
                                        <label>Сумма депозитов:</label>
                                        <input
                                                type="text"
                                                autocomplete="off"
                                                class="form-control"
                                                placeholder=""
                                                value="{{ $refData[4] }}"
                                                disabled />
                                    </div>

                                    <div class="col-lg-4">
                                        <label>Сумма выплат:</label>
                                        <input
                                                type="text"
                                                autocomplete="off"
                                                class="form-control"
                                                placeholder=""
                                                value="{{ $refData[5] }}"
                                                disabled />
                                    </div>
                                    <div class="col-lg-4">
                                        <label>Чистая прибыль:</label>
                                        <input
                                                type="text"
                                                autocomplete="off"
                                                class="form-control"
                                                placeholder=""
                                                value="{{ $refData[6] }}"
                                                disabled />
                                    </div>

                                </div>
                                <hr>
                                <div class="form-group row">
                                    <div class="col-lg-4">
                                        <label>Профит Dice:</label>
                                        <input
                                                type="text"
                                                autocomplete="off"
                                                class="form-control"
                                                placeholder=""
                                                value="{{ number_format($user->dice, 2, '.', '') }}"
                                                disabled />
                                    </div>
                                    <div class="col-lg-4">
                                        <label>Профит Mines:</label>
                                        <input
                                                type="text"
                                                autocomplete="off"
                                                class="form-control"
                                                placeholder=""
                                                value="{{ number_format($user->mines, 2, '.', '') }}"
                                                disabled />
                                    </div>
                                    <div class="col-lg-4">
                                        <label>Профит Slots:</label>
                                        <input
                                                type="text"
                                                autocomplete="off"
                                                class="form-control"
                                                placeholder=""
                                                value="{{ number_format($user->slots, 2, '.', '') }}"
                                                disabled />
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <div class="col-lg-4">
                                        <label>Профит Bubbles:</label>
                                        <input
                                                type="text"
                                                autocomplete="off"
                                                class="form-control"
                                                placeholder=""
                                                value="{{ number_format($user->bubbles, 2, '.', '') }}"
                                                disabled />
                                    </div>
                                    <div class="col-lg-4">
                                        <label>Профит Wheel:</label>
                                        <input
                                                type="text"
                                                autocomplete="off"
                                                class="form-control"
                                                placeholder=""
                                                value="{{ number_format($user->wheel, 2, '.', '') }}"
                                                disabled />
                                    </div>
                                    <div class="col-lg-4">
                                        <label>Профит Plinko:</label>
                                        <input
                                                type="text"
                                                autocomplete="off"
                                                class="form-control"
                                                placeholder=""
                                                value="{{ number_format($user->plinko, 2, '.', '') }}"
                                                disabled />
                                    </div>
                                </div>
                                <hr>
                                <div class="form-group row">
                                    <div class="col-lg-4">
                                        <label>Пополнил:</label>
                                        <input
                                                type="text"
                                                autocomplete="off"
                                                class="form-control"
                                                placeholder=""
                                                value="{{ round(\App\Models\Payment::query()->where([['user_id', $user->id], ['status', 1]])->sum('sum'), 2) }} р."
                                                disabled />
                                    </div>
                                    <div class="col-lg-4">
                                        <label>Вывел:</label>
                                        <input
                                                type="text"
                                                autocomplete="off"
                                                class="form-control"
                                                placeholder=""
                                                value="{{ round(\App\Models\Withdraw::query()->where([['user_id', $user->id], ['status', 1]])->sum('sum'), 2) }} р."
                                                disabled />
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane" id="userRoles" role="tabpanel">
                            <div class="kt-section">
                                <div class="form-group row">
                                    <div class="col-lg-4">
                                        <label>Администратор?</label>
                                        <select class="form-control" name="is_admin">
                                            <option value="1" @if($user->is_admin == 1) selected @endif>Да</option>
                                            <option value="0" @if($user->is_admin == 0) selected @endif>Нет</option>
                                        </select>
                                    </div>
                                    <div class="col-lg-4">
                                        <label>Стример?</label>
                                        <select class="form-control" name="is_youtuber">
                                            <option value="1" @if($user->is_youtuber == 1) selected @endif>Да</option>
                                            <option value="0" @if($user->is_youtuber == 0) selected @endif>Нет</option>
                                        </select>
                                    </div>
                                    <div class="col-lg-4">
                                        <label>Модератор?</label>
                                        <select class="form-control" name="admin_role">
                                            <option value="moder" @if($user->admin_role == 'moder') selected @endif>Да
                                            </option>
                                            <option value="" @if($user->admin_role != 'moder') selected @endif>Нет
                                            </option>
                                        </select>
                                    </div>
                                    <div class="col-lg-4">
                                        <label>Сотрудник?</label>
                                        <select class="form-control" name="is_worker">
                                            <option value="1" @if($user->is_worker == 1) selected @endif>Да</option>
                                            <option value="0" @if($user->is_worker == 0) selected @endif>Нет</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane" id="userLogs" role="tabpanel">
                            <div class="kt-section">
                                <table class="table table-striped- table-bordered table-hover table-checkable"
                                       id="userActions">
                                    <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Тип</th>
                                        <th>Баланс до</th>
                                        <th>Баланс после</th>
                                        <th>Дата</th>
                                    </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="kt-portlet__foot">
                    <div class="kt-form__actions">
                        <button type="submit" class="btn btn-primary">Сохранить</button>
                        <button type="button" class="btn btn-info" onclick="multiChecker({{$user->id}})">Найти
                            мультиаккаунты
                        </button>
                        <button type="button" class="btn btn-secondary"
                                onclick="window.location.href = '{{ route('admin.users') }}'">Назад
                        </button>
                        <div class="sm-btn-control d-flex flex-row">
                            <button type="button" class="btn btn-danger ml-5 fake"
                                    onclick="location.href = '{{ route('admin.users.createFake', ['type' => 'Pay', 'id' => $user->id]) }}'">
                                Добавить пополнение
                            </button>
                            <button type="button" class="btn btn-danger fake"
                                    onclick="location.href = '{{ route('admin.users.createFake', ['type' => 'Payout', 'id' => $user->id]) }}'">
                                Добавить выплату
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <?php
    function calculateReferralWithdraws($user) {
        $refs = \App\Models\User::where('referral_use', $user->id)->get();

        $totalEarnings = 0;

        foreach ($refs as $ref) {
            $earnings = \App\Models\Withdraw::where('user_id', $ref->id)
                ->where('status', 1)
                ->sum('sumWithCom');

            $totalEarnings += $earnings;
        }
        return $totalEarnings;
    }

    ?>
    <style>
        @media (max-width: 570px) {
            .ml-5.fake {
                margin-left: 0 !important;
            }

            .fake {
                margin: 30px 0 0 5px;
                min-width: 49.5%;
            }
        }

        @media (min-width: 570px) {
            .sm-btn-control {
                float: right;
                display: inline-block !important;
            }
        }
    </style>
    <script>
        $("#logs").DataTable();
        $(document).ready(function() {
            userActions({{ $user->id }});
        });

    </script>
    <script>
        function getRegDate(id) {
            $('#vkDate').html('...');
            $.post('/admin/getVKinfo', {
                vk_id: id
            })
                .then(res => {
                    $('#vkDate').html(res).css('pointerEvents', 'none');
                })
        }

        function getCountry(ip, elem) {
            $(elem).html('...');
            $.post('/admin/getCountry', {
                user_ip: ip
            })
                .then(res => {
                    $(elem).html(res).css('pointerEvents', 'none');
                })
        }
    </script>
    <style>
        @media (max-width: 1100px) {
            .col-lg-4 {
                margin-top: 20px;
            }
        }
    </style>
@endsection