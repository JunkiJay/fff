<?php

namespace App\Services\Telegram;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    protected $token;
    protected $apiUrl;

    public function __construct()
    {
        $this->token = config('services.telegram.token');
        $this->apiUrl = "https://api.telegram.org/bot{$this->token}/";
    }

    /**
     * Отправка сообщения
     */
    public function sendMessage(
        int $chatId,
        string $text,
        ?array $keyboard = null,
        bool $markdown = false
    ): ?array {
        $data = [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => $markdown ? 'MarkdownV2' : 'HTML'
        ];

        if ($keyboard) {
            $data['reply_markup'] = json_encode($keyboard);
        }

        return $this->sendRequest('sendMessage', $data);
    }

    /**
     * Отправка фото
     */
    public function sendPhoto(
        int $chatId,
        string $photo,
        ?string $caption = null
    ): ?array {
        $data = [
            'chat_id' => $chatId,
            'photo' => $photo,
        ];

        if ($caption) {
            $data['caption'] = $caption;
        }

        return $this->sendRequest('sendPhoto', $data);
    }

    /**
     * Отправка документа
     */
    public function sendDocument(
        int $chatId,
        string $document,
        ?string $caption = null
    ): ?array {
        $data = [
            'chat_id' => $chatId,
            'document' => $document,
        ];

        if ($caption) {
            $data['caption'] = $caption;
        }

        return $this->sendRequest('sendDocument', $data);
    }

    /**
     * Установка webhook
     */
    public function setWebhook(string $url): ?array
    {
        return $this->sendRequest('setWebhook', [
            'url' => $url,
            'allowed_updates' => ['message', 'callback_query']
        ]);
    }

    /**
     * Удаление webhook
     */
    public function deleteWebhook(): ?array
    {
        return $this->sendRequest('deleteWebhook');
    }

    /**
     * Получение информации о боте
     */
    public function getMe(): ?array
    {
        return $this->sendRequest('getMe');
    }

    /**
     * Создание клавиатуры
     */
    public function createKeyboard(array $buttons, bool $resize = true): array
    {
        return [
            'keyboard' => $buttons,
            'resize_keyboard' => $resize
        ];
    }

    /**
     * Создание инлайн клавиатуры
     */
    public function createInlineKeyboard(array $buttons): array
    {
        return [
            'inline_keyboard' => $buttons
        ];
    }

    /**
     * Удаление клавиатуры
     */
    public function removeKeyboard(): array
    {
        return [
            'remove_keyboard' => true
        ];
    }

    /**
     * Отправка запроса к API
     */
    protected function sendRequest(string $method, array $data = []): ?array
    {
        try {
            $response = Http::post($this->apiUrl . $method, $data);

            if (!$response->successful()) {
                Log::error('Telegram API Error', [
                    'method' => $method,
                    'data' => $data,
                    'response' => $response->json()
                ]);
                return null;
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::error('Telegram Request Error', [
                'method' => $method,
                'data' => $data,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Ответ на callback query
     */
    public function answerCallbackQuery(
        string $callbackQueryId,
        ?string $text = null,
        bool $showAlert = false
    ): ?array {
        $data = ['callback_query_id' => $callbackQueryId];

        if ($text) {
            $data['text'] = $text;
            $data['show_alert'] = $showAlert;
        }

        return $this->sendRequest('answerCallbackQuery', $data);
    }

    /**
     * Редактирование сообщения
     */
    public function editMessageText(
        int $chatId,
        int $messageId,
        string $text,
        ?array $keyboard = null
    ): ?array {
        $data = [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => $text
        ];

        if ($keyboard) {
            $data['reply_markup'] = json_encode($keyboard);
        }

        return $this->sendRequest('editMessageText', $data);
    }

    /**
     * Удаление сообщения
     */
    public function deleteMessage(int $chatId, int $messageId): ?array
    {
        return $this->sendRequest('deleteMessage', [
            'chat_id' => $chatId,
            'message_id' => $messageId
        ]);
    }

    /**
     * Получение информации о чате
     */
    public function getChat(int $chatId): ?array
    {
        return $this->sendRequest('getChat', [
            'chat_id' => $chatId
        ]);
    }

    /**
     * Отправка действия (например, печатает...)
     */
    public function sendChatAction(int $chatId, string $action = 'typing'): ?array
    {
        return $this->sendRequest('sendChatAction', [
            'chat_id' => $chatId,
            'action' => $action
        ]);
    }
}
