<?php

namespace App\Facades\Helpers;

use Illuminate\Support\Facades\Http;

class TelegramHelper
{
    public static function getUserIdByUsername($username)
    {
        $url = "http://91.84.105.200/get_id";

        $data = [
            'username' => $username
        ];

        try {

            $result = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post($url, $data);

        } catch (\Exception $e) {
            return [
                'error' => true,
                'message' => 'Не смогли определить ваш телеграм ид по никнейму, введите Telegram ID! ' . $e->getMessage()
            ];
        }

        $response = json_decode($result, true);

        if ($response['id']) {
            return $response['id'];
        } else {
            return [
                'error' => true,
                'message' => 'Не смогли найти ваш телеграм ид, обратитесь в техподдержку! '
            ];
        }
    }
}