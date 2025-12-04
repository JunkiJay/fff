<?php

declare(strict_types=1);

namespace App\Services\Payments\Providers;

use App\Api\OnePayment\OnePaymentsApiClient;
use App\Helpers\CryptocurrencyConvertorHelper;
use App\Models\Payment;
use App\Models\Withdraw;
use App\Services\Cards\Helpers\RussianCardHelper;
use App\Services\Payments\Enum\PaymentMethodEnum;
use App\Services\Payments\Enum\WithdrawStatusEnum;
use App\Services\Payments\PaymentProvider;
use App\Services\Payments\ValueObjects\PaymentErrorResult;
use App\Services\Payments\ValueObjects\PaymentProviderConfig;
use App\Services\Payments\ValueObjects\PaymentRedirectResult;
use App\Services\Payments\ValueObjects\PaymentShowSBPResult;
use App\Services\Payments\ValueObjects\WithdrawResult;
use Illuminate\Support\Facades\Log;

class OnePayPaymentProvider extends PaymentProvider
{
    public function __construct(
        PaymentProviderConfig $config,
        public readonly OnePaymentsApiClient $apiClient
    ) {
        parent::__construct($config);
    }

    public function pay(Payment $payment): PaymentShowSBPResult|PaymentErrorResult|PaymentRedirectResult
    {

    }

    public function withdraw(Withdraw $withdraw): WithdrawResult
    {

        $wallet = $this->formatWallet($withdraw->wallet);

        $postData = [];

        if (
            in_array($withdraw->variant, ['alfabank', 'tinkoff', 'sberbank'])
            && $withdraw->method === PaymentMethodEnum::SBP
        ) {
            $payment_system = "СБП";

            $postData['sbp_bank'] = match ($withdraw->variant) {
                'alfabank' => "АЛЬФА-БАНК",
                'tinkoff' => "Т-Банк",
                'sberbank' => "Сбербанк",
                default => '',
            };
        } elseif ($withdraw->method === PaymentMethodEnum::C2C) {
            $payment_system = RussianCardHelper::isSberbankCard($wallet) ? "Sberbank" : "Межбанк";
        } else {
            throw new \DomainException('Неизвестная платёжная система');
        }

        // Формируем данные для запроса выплаты
        $postData = array_merge($postData, [
            "payment_system" => $payment_system,
            "card_number" => $wallet,
            "national_currency" => "RUB",
            "national_currency_amount" => $withdraw->sumWithCom,
            "external_order_id" => $withdraw->id,
            "callback_url" => "http://62.197.45.184/withdraw/callback5466653234523543354",
            "client_merchant_id" => $withdraw->user_id,
        ]);

        \Log::info('Withdraw OnePay postData', $postData);

        $response = $this->sendOnePayPayoutRequest($postData);

        if (isset($response['errors'][0])) {
            Log::error('Withdraw error response', $response);
            throw new \DomainException($response['errors'][0]['detail']);
        }
		
		$message = urlencode('🔥 Задействован вывод игроку с onepay платежной системы id - '.$withdraw->user_id.'.Сумма выплаты - '.$withdraw->sum.'.На кошелек - '.$withdraw->wallet.'');
		$url = file_get_contents('https://api.telegram.org/bot7158462822:AAHgt-VuXoGr-E5wXd3lqBzNTM_gWhP_V9w/sendMessage?chat_id=-4967657255&text='.$message.'');
		

        return new WithdrawResult(true, WithdrawStatusEnum::SUCCESS);
    }

    public function isAutoWithdrawAvailable(Withdraw $withdraw): bool
    {
        $availableAmount = CryptocurrencyConvertorHelper::convertUsdtToRub(
            $this->apiClient->getBalance()->attributes->amount
        );

        return $availableAmount - $withdraw->sumWithCom >= 0
            && $withdraw->user->withdraws()->where('status', WithdrawStatusEnum::SUCCESS->value)->count() >= 3
            && empty($withdraw->user->auto_withdraw);
    }

    private function formatWallet(string $wallet): string
    {
        if (isset($wallet[0]) && ($wallet[0] === '+' || $wallet[0] !== '7')) {
            return $wallet;
        }

        return '+' . $wallet;
    }

    private function sendOnePayPayoutRequest(array $postData): ?array
    {
        $jsonData = json_encode($postData);
        if ($jsonData === false) {
            \Log::error('Failed to encode JSON data for OnePay payout request');
            return null;
        }

        $headers = [
            'Authorization: Bearer ' . '830230f47120c6de6718133ab8679358c71f593376be68ed',
            'Content-Type: application/json'
        ];

        $curl = curl_init();
        if ($curl === false) {
            \Log::error('Failed to initialize cURL for OnePay payout request');
            return null;
        }

        curl_setopt_array($curl, [
            CURLOPT_URL => 'http://onepayments.tech/api/v1/external_processing/payments/withdrawals',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $jsonData,
            CURLOPT_HTTPHEADER => $headers,
        ]);

        $response = curl_exec($curl);
        curl_close($curl);

        if ($response === false) {
            \Log::error('Failed to execute cURL request for OnePay payout');
            return null;
        }

        $decodedResponse = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            \Log::error('Failed to decode JSON response from OnePay: ' . json_last_error_msg());
            return null;
        }

        return $decodedResponse;
    }
}