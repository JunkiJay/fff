<?php

namespace App\Http\Controllers;

use App\Models\Action;
use App\Models\BonuseLog;
use App\Models\BonusLevel;
use App\Models\Payment;
use App\Models\Post;
use App\Models\User;
use DB;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class BonusController extends Controller
{
    public function init()
    {
        return [
            'repostInfo' => [
                'total' => (float) $this->user->total_reposts,
                'levels' => BonusLevel::orderBy('goal', 'asc')->get(),
                'bonusBalance' => (float) $this->user->bonus_balance
            ],
            'bonuses' => [
                'daily' => $this->user->bonus_daily,
                'hourly' => $this->user->bonus_hourly,
                'one' => $this->user->bonus_use ? 86400 * 999999 : 0
            ],
            'onetime' => $this->config->onetime_bonus
        ];
    }

    public function take(Request $request)
    {
        if (!in_array($request->type, ['daily', 'hourly', 'one']))
            return;

        if (
            auth()->user()->vk_id
            && !$this->isGroupMember()
        ) {
            return [
                'error' => true,
                'message' => 'Подпишитесь на группу VK'
            ];
        }

        if (!$this->user->tg_id) {
            return [
                'error' => true,
                'message' => 'Привяжите аккаунт Telegram',
                'showModal' => true
            ];
        }

        if (!$this->isChannelMember()) {
            return [
                'error' => true,
                'message' => 'Подпишитесь на Telegram канал'
            ];
        }

        if ($this->user->balance >= 10) {
            return [
                'error' => true,
                'message' => 'Нельзя получить бонус, если ваш баланс более 10!'
            ];
        }

        if (\App\Models\Mine::where('status', 0)->where('user_id', $this->user->id)->first()) {
            return [
                'error' => true,
                'message' => 'Завершите игру в Mines'
            ];
        }

        try {
            DB::beginTransaction();

            $user = User::lockForUpdate()->where('id', $this->user->id)->first();
            $remaining = null;

            switch ($request->type) {
                case 'daily':
                    if ($user->bonus_daily > time()) {
                        DB::rollback();
                        return [
                            'error' => true,
                            'message' => 'Бонус можно получить через ' . $this->remainingTime($user->bonus_daily - time())
                        ];
                    }

                    $psum = Payment::where([['created_at', '>=', \Carbon\Carbon::today()->subDays(7)], ['user_id', $user->id], ['status', 1]])->sum('sum');

                    if ($psum < 500) {
                        DB::rollback();
                        return [
                            'error' => true,
                            'message' => 'Необходимо пополнить баланс на 500 руб за последние 7 дней'
                        ];
                    }

                    $bonus = rand(
                        $this->config->daily_bonus_min * 100,
                        $this->config->daily_bonus_max * 100
                    ) / 100;

                    Action::create([
                        'user_id' => $user->id,
                        'action' => 'Бонус daily (+' . $bonus . ')',
                        'balanceBefore' => $user->balance,
                        'balanceAfter' => $user->balance + $bonus
                    ]);

                    $user->balance += $bonus;
                    $user->bonus_daily = time() + 86400;
                    $user->save();

                    BonuseLog::create([
                        'user_id' => $user->id,
                        'type' => 'daily',
                        'size' => round($bonus, 2)
                    ]);

                    $remaining = $user->bonus_daily;
                    break;

                case 'hourly':
                    if ($user->bonus_hourly > time()) {
                        DB::rollback();
                        return [
                            'error' => true,
                            'message' => 'Бонус можно получить через ' . $this->remainingTime($user->bonus_hourly - time())
                        ];
                    }

                    $bonus = rand(
                        $this->config->hourly_bonus_min * 100,
                        $this->config->hourly_bonus_max * 100
                    ) / 100;

                    Action::create([
                        'user_id' => $user->id,
                        'action' => 'Бонус hourly (+' . $bonus . ')',
                        'balanceBefore' => $user->balance,
                        'balanceAfter' => $user->balance + $bonus
                    ]);

                    $user->balance += $bonus;
                    $user->bonus_hourly = time() + 3600;
                    $user->save();

                    $remaining = $user->bonus_hourly;
                    break;

                case 'one':
                    if ($user->bonus_use) {
                        DB::rollback();
                        return [
                            'error' => true,
                            'message' => 'Вы уже получали бонус'
                        ];
                    }

                    $bonus = $this->config->onetime_bonus;

                    Action::create([
                        'user_id' => $user->id,
                        'action' => 'Бонус one (+' . $bonus . ')',
                        'balanceBefore' => $user->balance,
                        'balanceAfter' => $user->balance + $bonus
                    ]);

                    $user->balance += $bonus;
                    $user->bonus_use = 1;
                    $user->save();

                    BonuseLog::create([
                        'user_id' => $user->id,
                        'type' => 'onetime',
                        'size' => round($bonus, 2)
                    ]);
                    break;
            }

            // $twink = User::where('created_ip', $this->user->created_ip)->where('id', '!=', $this->user->id)->first();
            // if($twink){
            //     DB::rollback();
            //     return [
            //         'error' => true,
            //         'message' => 'Для вашего аккаунта действует ограничение на бонусы, пожалуйста играйте на "Пополнение"'
            //     ];
            // }

            if (!$user->referral_send && $user->referral_use) {
                $refer = User::find($user->referral_use);
                $refer->increment('referral_balance', $this->config->referral_reward);

                $user->referral_send = 1;
                $user->save();

                \App\Models\ReferralProfit::create([
                    'from_id' => $user->id,
                    'ref_id' => $refer->id,
                    'amount' => $this->config->referral_reward,
                    'level' => 1
                ]);
            }

            if (!(\Cache::has('user.' . $this->user->id . '.historyBalance'))) {
                \Cache::put('user.' . $this->user->id . '.historyBalance', '[]');
            }

            $hist_balance = array(
                'user_id' => $this->user->id,
                'type' => 'Бонус ' . $request->type,
                'balance_before' => round($user->balance - $bonus, 2),
                'balance_after' => round($user->balance, 2),
                'date' => date('d.m.Y H:i:s')
            );

            $cashe_hist_user = \Cache::get('user.' . $this->user->id . '.historyBalance');

            $cashe_hist_user = json_decode($cashe_hist_user);
            $cashe_hist_user[] = $hist_balance;
            $cashe_hist_user = json_encode($cashe_hist_user);
            \Cache::put('user.' . $this->user->id . '.historyBalance', $cashe_hist_user);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            return [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }

        return [
            'balance' => $user->balance,
            'text' => 'Вы получили ' . $bonus,
            'type' => $request->type,
            'remaining' => $remaining
        ];
    }

    public function transfer()
    {
        try {
            DB::beginTransaction();

            $user = User::lockForUpdate()->where('id', $this->user->id)->first();

            if ($user->bonus_balance < 5) {
                DB::rollback();
                return [
                    'error' => true,
                    'message' => 'Минимум 5'
                ];
            }

            $bonus = $user->bonus_balance;

            $user->balance += $user->bonus_balance;
            $user->bonus_balance = 0;
            $user->save();

            if (!(\Cache::has('user.' . $this->user->id . '.historyBalance'))) {
                \Cache::put('user.' . $this->user->id . '.historyBalance', '[]');
            }

            $hist_balance = array(
                'user_id' => $this->user->id,
                'type' => 'Перевод репостов',
                'balance_before' => round($user->balance - $bonus, 2),
                'balance_after' => round($user->balance, 2),
                'date' => date('d.m.Y H:i:s')
            );

            BonuseLog::create([
                'user_id' => $this->user->id,
                'type' => 'repost',
                'size' => round($bonus, 2)
            ]);

            $cashe_hist_user = \Cache::get('user.' . $this->user->id . '.historyBalance');

            $cashe_hist_user = json_decode($cashe_hist_user);
            $cashe_hist_user[] = $hist_balance;
            $cashe_hist_user = json_encode($cashe_hist_user);
            \Cache::put('user.' . $this->user->id . '.historyBalance', $cashe_hist_user);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            return [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }

        return [
            'balance' => $user->balance
        ];
    }

    public function checkReposts()
    {
        if (
            auth()->user()->vk_id
            && !$this->isGroupMember()
        ) {
            return [
                'error' => true,
                'message' => 'Подпишитесь на группу VK'
            ];
        }


        try {
            DB::beginTransaction();

            $user = User::lockForUpdate()->where('id', $this->user->id)->first();
            $level = $this->getLevel();

            $newRepost = $this->countPosts();



            if (count($newRepost) == 0) {
                return [
                    'error' => true,
                    'message' => 'Новых действий не обнаружено'
                ];
            }

            foreach ($newRepost as $id) {
                Post::create(['owner_id' => $this->user->id, 'post_id' => $id]);
            }

            $user->increment('bonus_balance', count($newRepost) * $level->reward);
            $user->increment('total_reposts', count($newRepost));

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            return [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }

        return [
            'total' => $user->total_reposts,
            'bonusBalance' => $user->bonus_balance
        ];
    }

    private function getLevel()
    {
        $levels = BonusLevel::orderBy('goal', 'asc')->get();

        if (!count($levels)) {
            throw new HttpException(500, 'Администратор не добавил ни одного уровня');
        }

        $bonusId = $levels[0]->id;
        $bonusMax = $levels[count($levels) - 1];

        foreach ($levels as $index => $level) {
            if (
                $levels[$index === 0 ? 0 : $index - 1]->goal <= $this->user->total_reposts &&
                $level->goal > $this->user->total_reposts
            )
                $bonusId = $level->id;
        }

        if ($this->user->total_reposts >= $bonusMax->goal) {
            $bonusId = $bonusMax->id;
        }

        return BonusLevel::find($bonusId);
    }

    private function getPosts($owner_id)
    {
        $data = [
            'owner_id' => $owner_id,
            'access_token' => $this->config->vk_service_token,
            'count' => 15,
            'v' => '5.131'
        ];

        $posts = $this->curl('https://api.vk.com/method/wall.get?' . http_build_query($data));

        if (isset($posts['error'])) {
            if ($posts['error']['error_msg'] == 'Access denied: user hid his wall from accessing from outside') {
                throw new HttpException(500, 'Включите параметр в настройках, чтобы ваша страница была видна всем и откройте профиль');
            } else {
                throw new HttpException(500, $posts['error']['error_msg']);
            }

        }

        return $posts['response']['items'];
    }

    private function countPosts()
    {


        $userPosts = $this->getPosts($this->user->vk_id);

        $groupPosts = $this->getPosts(-$this->config->vk_id);

        $userID = [];
        $groupID = [];

        $newID = [];


        foreach ($userPosts as $userPost) { // получаем id репостнутой записи
            @$userID[] = $userPost['copy_history'][0]['id'];
        }

        foreach ($groupPosts as $groupPost) { // получаем id записи в сообществе
            @$groupID[] = $groupPost['id'];
        }

        foreach ($userID as $id) {
            if (in_array($id, $newID))
                continue;
            if (!in_array($id, $groupID))
                continue;
            if (Post::where('owner_id', $this->user->id)->where('post_id', $id)->count() !== 0)
                continue;

            $newID[] = $id;
        }

        return $newID;
    }
}
