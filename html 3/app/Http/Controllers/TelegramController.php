<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\TelegramBinding;
use App\Models\User;
use App\Models\VipInvite;
use App\Services\Telegram\TelegramService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Log;

class TelegramController extends Controller
{
    protected $telegram;

    public function __construct(TelegramService $telegram)
    {
        $this->telegram = $telegram;
    }

    public function getUserId(Request $request) {
        $username = $request->username;
        return $username;
    }

    protected function sendWelcomeMessage($chatId)
    {
        $user = User::where('tg_id', $chatId)->first();
        $isVip = $user && $this->isVipUser($user);
        $depositSum = $user ? Payment::where('status', 1)->where('user_id', $user->id)->sum('sum') : 0;
    
        $message = "🤩 <b>Добро пожаловать в официальный Telegram Bot сайта Stimule!</b>\n\n";
        $message .= "📌 <b>Актуальные ссылки:</b>\n";
        $message .= "Домен <b>Stimule.win</b> | TG <b>@stimule_tg</b>\n";
        $message .= "Vk Группа <b>vk.com/club225380369</b>\n\n";
    
        if ($isVip) {
            $message .= "🎉 При достижении 10.000 руб депозитов, можно вступить в Vip Клуб, не стесняйтесь писать своему личному менеджеру за бонусами. (Только участникам Vip Клуба)!\n\n";
        }
    
        // Inline клавиатура
        $inlineKeyboard = [
            [
                [
                    'text' => $user ? '✅ Аккаунт привязан' : '❌ Аккаунт не привязан',
                    'callback_data' => 'account_status'
                ],
                [
                    'text' => '💎 VIP Клуб',
                    'callback_data' => 'vip_club'
                ]
            ]
        ];
    
        if ($isVip) {
            $inlineKeyboard[] = [
                [
                    'text' => '👩‍💻 Личный менеджер',
                    'callback_data' => 'personal_manager'
                ]
            ];
        }
    
        // Reply клавиатура
        $replyKeyboard = [
            'keyboard' => [
                [['text' => '📊 Статистика'], ['text' => '💎 VIP статус']],
                [['text' => '👤 Профиль'], ['text' => '⚙️ Настройки']]
            ],
            'resize_keyboard' => true
        ];
    
        // Отправка сообщения с inline клавиатурой
        $this->telegram->sendMessage($chatId, $message, ['inline_keyboard' => $inlineKeyboard]);
        
        return response()->json(['status' => 'ok']);
    }

    /**
     * Проверка VIP-статуса пользователя (депозитов >= 10 000)
     */
    protected function isVipUser(User $user): bool
    {
        $depositSum = Payment::where('status', 1)
            ->where('user_id', $user->id)
            ->where('created_at', '>=', Carbon::create(2025, 6, 26, 0, 0, 0))
            ->sum('sum');
        return $depositSum >= 10000;
    }


    public function handle(Request $request)
    {
        Log::debug('Text message received:', $request->all());
        $update = $request->all();

        if (isset($update['message'])) {
            return $this->handleMessage($update['message']);
        }

        if (isset($update['callback_query'])) {
            return $this->handleCallbackQuery($update['callback_query']);
        }

        return response()->json(['status' => 'ok']);
    }

    /**
     * Обработка текстовых сообщений
     */
    protected function handleMessage($message)
    {

        $chatId = $message['chat']['id'];
        $text = $message['text'] ?? '';

        if (strpos($text, '/start') === 0) {
            $code = trim(substr($text, 7));
            if ($code !== '') {
                return $this->handleBinding($chatId, $code);
            }
            return $this->sendWelcomeMessage($chatId);
        }

        // Привязка по команде /bind <unique_id>
        if (preg_match('/^\/bind\s+(\S+)/ui', $text, $matches)) {
            $uniqueId = $matches[1];
            return $this->handleBindByUniqueId($chatId, $uniqueId);
        }

        // Handle reply keyboard button clicks
        switch (trim($text)) {
            case '📊 Статистика':
                return $this->showStats($chatId);
            case '💎 VIP статус':
                return $this->showVipStatus($chatId);
            case '👤 Профиль':
                return $this->showProfile($chatId);
            case '⚙️ Настройки':
                return $this->showSettings($chatId);
        }

        return $this->handleTextMessage($chatId, $text);
    }

    /**
     * Привязка Telegram-аккаунта по временному коду
     */
    protected function handleBinding($chatId, $code)
    {
        $binding = TelegramBinding::where('code', $code)
            ->where('expires_at', '>', now())
            ->first();

        if (!$binding) {
            $this->telegram->sendMessage($chatId, '❌ Код недействителен или истёк. Запросите новый код на сайте.');
            return response()->json(['status' => 'error']);
        }

        $user = User::find($binding->user_id);
        if (!$user) {
            $this->telegram->sendMessage($chatId, '❌ Пользователь не найден.');
            return response()->json(['status' => 'error']);
        }

        $user->tg_id = $chatId;
        $user->save();
        $binding->delete(); // Инвалидируем код

        $message = "✅ Аккаунт успешно привязан!\n\n";
        $message .= "🎉 Добро пожаловать в VIP клуб!";

        $this->telegram->sendMessage($chatId, $message);

        return response()->json(['status' => 'ok']);
    }


    
    protected function handleTextMessage($chatId, $text)
    {
        // Handle any direct text messages if needed
        return $this->sendWelcomeMessage($chatId);
    }

    /**
     * Handle inline keyboard callbacks
     */
    protected function handleCallbackQuery($callbackQuery)
    {
        $chatId = $callbackQuery['message']['chat']['id'];
        $data = $callbackQuery['data'];
        $messageId = $callbackQuery['message']['message_id'];
        $callbackQueryId = $callbackQuery['id'];

        $user = User::where('tg_id', $chatId)->first();
        $isVip = $user && $this->isVipUser($user);

        switch ($data) {
            case 'account_status':
                $this->telegram->answerCallbackQuery($callbackQueryId);
                if ($user) {
                    return $this->showAccountStatus($chatId, $user);
                } else {
                    return $this->telegram->sendMessage($chatId, '❌ Заберите первый бонус на сайте, в бота необходимо отправить команду /bind и с уникальным кодом.');
                }
            case 'vip_club':
                $this->telegram->answerCallbackQuery($callbackQueryId);
                if ($user) {
                    return $this->showVipStatus($chatId);
                } else {
                    return $this->telegram->sendMessage($chatId, '❌ Пожалуйста, привяжите аккаунт для доступа к VIP Клубу');
                }
            case 'personal_manager':
                $this->telegram->answerCallbackQuery($callbackQueryId);
                if ($isVip) {
                    return $this->showPersonalManager($chatId);
                } else {
                    return $this->telegram->sendMessage($chatId, '❌ Эта функция доступна только для VIP пользователей');
                }


            // Deposit
            case 'deposit':
                return $this->handleDeposit($chatId, $messageId, $callbackQueryId);

            // Settings
            case 'settings_notifications':
                return $this->handleNotificationSettings($chatId, $messageId, $callbackQueryId);
            case 'settings_security':
                return $this->handleSecuritySettings($chatId, $messageId, $callbackQueryId);
            case 'settings_unbind':
                return $this->handleUnbind($chatId, $messageId, $callbackQueryId);
        }

        $this->telegram->answerCallbackQuery($callbackQueryId);
        return response()->json(['status' => 'ok']);
    }

    
    protected function showStats($chatId)
    {
        $user = User::where('tg_id', $chatId)->first();

        if (!$user) {
            return $this->telegram->sendMessage(
                $chatId,
                '❌ Аккаунт не найден'
            );
        }

        $depositSum = Payment::where('status', 1)
        ->where('user_id', $user->id)
        ->sum('sum');

        $message = "📊 Ваша статистика:\n\n";
        $message .= "💰 Общая сумма депозитов: {$depositSum}₽\n";
        $message .= "🎯 Статус: " . ($depositSum >= 10000 ? "VIP" : "Обычный пользователь");

        return $this->telegram->sendMessage($chatId, $message);
    }

    
    protected function showVipStatus($chatId)
    {
        $user = User::where('tg_id', $chatId)->first();

        if (!$user) {
            return $this->telegram->sendMessage(
                $chatId,
                '❌ Аккаунт не найден. Пожалуйста, привяжите аккаунт на сайте.'
            );
        }

        $depositSum = Payment::where('status', 1)
            ->where('user_id', $user->id)
            ->sum('sum');

        $isVip = $depositSum >= 10000;

        if ($isVip) {
            // Get or create VIP invite link
            $invite = VipInvite::where('user_id', $user->id)
                ->where('is_active', 1)
                ->orderByDesc('id')
                ->first();

            if ($invite && $invite->invite_link) {
                $link = $invite->invite_link;
            } else {
                try {
                    $tgInvite = Http::post(env('TELEGRAM_API_URL') . env('TELEGRAM_BOT_TOKEN') . "/createChatInviteLink", [
                        'chat_id' => intval(env('TELEGRAM_VIP_CHAT_ID')),
                        'member_limit' => 1,
                        'creates_join_request' => false,
                    ]);

                    Log::debug($tgInvite);
                    $link = $tgInvite['result']['invite_link'];
                    
                    VipInvite::create([
                        'user_id' => $user->id,
                        'invite_link' => $link,
                        'created_at' => now(),
                        'is_active' => 1,
                    ]);
                } catch (Exception $e) {
                    Log::error('Error creating VIP invite link: ' . $e->getMessage());
                    $link = 'https://t.me/stimule_tg';
                }
            }

            $message = "🎉 <b>Поздравляем вы стали участником закрытого Vip-Клуба stimule!</b>\n\n";
            $message .= "Новые возможности:\n\n";
            $message .= "💎 <b>Доступ в закрытую Vip группу!</b>\n";
            $message .= "✅ Открыт личный менеджер, который отвечает на ваши вопросы и может выдать персональный бонус!\n";
            $message .= "✅ Повышенный кешбек до 15%\n";
            $message .= "🔜 <b>Следите за группой и не пропустите грандиозные события, которые будут скоро запущенны!</b>\n\n";
            $message .= "🔗 <b>Ссылка на группу:</b> {$link}";
            
        } else {
            $remaining = 10000 - $depositSum;
            
            $message = "💫 <b>Стань участником VIP-Клуба!</b>\n\n";
            $message .= "Пополните счет на общую сумму в <b>10.000 Рублей</b> и получите возможность присоединиться к закрытому VIP-Клубу STIMULE\n\n";
            $message .= "<b>Ваш текущий депозит:</b> {$depositSum} RUB\n";
            $message .= "<b>Необходимо до VIP:</b> " . (10000 - $depositSum) . " RUB\n\n";
            $message .= "💡 <b>По достижению 10.000 RUB ваш статус автоматически изменится на VIP, и вам станут доступны все привилегии клуба!</b>";
            $keyboard = [
                'inline_keyboard' => [
                    [['text' => '💳 Пополнить баланс', 'url' => 'https://stimule.win/pay']]                ]
            ];

            return $this->telegram->sendMessage($chatId, $message, $keyboard);
        }

        return $this->telegram->sendMessage($chatId, $message);
    }

    protected function showProfile($chatId)
    {
        $user = User::where('tg_id', $chatId)->first();

        if (!$user) {
            return $this->telegram->sendMessage(
                $chatId,
                '❌ Аккаунт не найден'
            );
        }

        $message = "👤 Ваш профиль:\n\n";
        $message .= "🆔 ID: {$user->id}\n";
        $message .= "📧 Email: {$user->email}\n";
        $message .= "📅 Дата регистрации: " . $user->created_at->format('d.m.Y');

        return $this->telegram->sendMessage($chatId, $message);
    }


    protected function showSettings($chatId)
    {
        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => '🔔 Уведомления', 'callback_data' => 'settings_notifications'],
                    ['text' => '🔑 Безопасность', 'callback_data' => 'settings_security']
                ],
                [
                    ['text' => '❌ Отвязать аккаунт', 'callback_data' => 'settings_unbind']
                ]
            ]
        ];

        $message = "⚙️ Настройки:\n\n";
        $message .= "Выберите раздел настроек:";

        return $this->telegram->sendMessage($chatId, $message, $keyboard);
    }

    protected function handleUnbind($chatId, $messageId, $callbackQueryId)
    {
        $user = User::where('tg_id', $chatId)->first();

        if (!$user) {
            $this->telegram->editMessageText($chatId, $messageId, "❌ Аккаунт не найден.");
            $this->telegram->answerCallbackQuery($callbackQueryId, "Ошибка!");
            return response()->json(['status' => 'error']);
        }

        $user->tg_id = null;
        $user->save();

        $message = "🔓 Ваш Telegram-аккаунт успешно отвязан!\n\nЧтобы привязать снова — используйте команду /start на сайте.";
        $this->telegram->editMessageText($chatId, $messageId, $message);
        $this->telegram->sendMessage($chatId, "Теперь вы можете использовать команды бота в любом чате.");
        $this->telegram->answerCallbackQuery($callbackQueryId, "Telegram отвязан!");

        return response()->json(['status' => 'ok']);
    }

    
    /**
     * Show account status information
     */
    protected function showAccountStatus($chatId, $user)
    {
        $depositSum = Payment::where('status', 1)
            ->where('user_id', $user->id)
            ->sum('sum');

        $message = "👤 <b>Статус аккаунта</b>\n\n";
        $message .= "🆔 ID: <code>{$user->id}</code>\n";
        $message .= "📧 Email: <code>{$user->email}</code>\n";
        $message .= "💳 Баланс: <b>{$user->balance} RUB</b>\n";
        $message .= "💎 VIP статус: " . ($this->isVipUser($user) ? '✅ Активен' : '❌ Не активен') . "\n";
        $message .= "📅 Дата регистрации: " . $user->created_at->format('d.m.Y');

        return $this->telegram->sendMessage($chatId, $message);
    }

    /**
     * Show personal manager information
     */
    protected function showPersonalManager($chatId)
    {
        $message = "👩‍💻 <b>Ваш личный менеджер скоро будет добавлен (Он будет выдавать персональные бонусы)</b>\n\n";
        $message .= "сейчас вы можете обратиться в поддержку в группу вк, для решения любого вопроса\n\n";
        $message .= "<a href='vk.com/im?sel=-225111416'>vk.com/im?sel=-225111416</a>";

        return $this->telegram->sendMessage($chatId, $message);
    }

    protected function handleDeposit($chatId, $messageId, $callbackQueryId)
    {
        $message = "💳 <b>Пополнение баланса</b>\n\n";
        $message .= "Для пополнения баланса перейдите по ссылке:\n";
        $message .= "🔗 <a href='https://stimule.win/pay>Пополнить баланс</a>";

        $this->telegram->editMessageText($chatId, $messageId, $message);
        $this->telegram->answerCallbackQuery($callbackQueryId, "Открываю форму пополнения");

        return response()->json(['status' => 'ok']);
    }

    /**
     * Привязка Telegram-аккаунта по unique_id (команда /bind <code>)
     */

    protected function handleBindByUniqueId(int $chatId, string $uniqueId)
    {
        return DB::transaction(function () use ($chatId, $uniqueId) {
            // Найти пользователя по уникальному коду
            $user = User::where('unique_id', $uniqueId)->first();

            if (!$user) {
                $this->telegram->sendMessage($chatId, '❌ Пользователь с таким кодом не найден. Проверьте правильность кода.');
                return response()->json(['status' => 'error', 'message' => 'User not found by unique_id']);
            }

            // Проверить, не привязан ли уже этот tg_id к другому пользователю
            $tgIdExists = User::where('tg_id', $chatId)
                ->where('id', '!=', $user->id)
                ->lockForUpdate()
                ->exists();
            if ($tgIdExists) {
                $this->telegram->sendMessage($chatId, '❌ Этот Telegram-аккаунт уже привязан к другому профилю.');
                return response()->json(['status' => 'error', 'message' => 'tg_id already linked to another user']);
            }

            // Привязать tg_id к текущему пользователю
            $user->tg_id = $chatId;
            $user->save();

            $message = "✅ Аккаунт успешно привязан!\n\n";
            $message .= "📌 Подпишитесь на TG @stimule_tg чтобы быть в курсе всех новостей и получить первый бонус!";
            $this->telegram->sendMessage($chatId, $message);

            return response()->json(['status' => 'ok']);
        });
    }
}

