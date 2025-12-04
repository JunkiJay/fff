<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Services\BovaPayment\BovaPaymentService;
use App\Services\Notifications\Facades\NotificationsServiceFacade;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class BovaPaymentController extends Controller
{
    private $paymentService;

    public function __construct(BovaPaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }


    public function createPayment(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'amount' => 'required|numeric|min:1',
                'system' => 'required|string'
            ]);

            if ($validator->fails()) {
                return [
                    'error' => true,
                    'message' => $validator->errors()->first()
                ];
            }

            $amount = $request->amount;
            $user = $request->user();

            $payment = Payment::create([
                'user_id' => $user->id,
                'sum' => $amount,
                'bonus' => 1,
                'wager' => 3,
                'system' => $request->system,
                'status' => 0
            ]);

            $paymentData = [
                'user_uuid' => config('services.bova.user_uuid'),
                'merchant_id' => (string)$payment->id,
                'payeer_identifier' => (string)$user->id,
                'payeer_ip' => $request->ip(),
                'payeer_type' => 'ftd',
                'currency' => 'rub',
                'payment_method' => 'click',
                'payeer_bank_name' => 'tinkoff',
                'amount' => $amount,
                'callback_url' => config('app.url') . '/bova/webhook',
                'redirect_url' => config('app.url') . '/pay',
                "email" => $user->email
            ];

            $response = $this->paymentService->createPayment($paymentData);

            return $response;
        } catch (Exception $e) {
            Log::error('Payment creation failed', [
                'error' => $e->getMessage(),
                'user_id' => $user->id ?? null,
                'amount' => $amount ?? null
            ]);

            return [
                'error' => true,
                'message' => 'Payment creation failed: ' . $e->getMessage()
            ];
        }
    }


    public function handleWebhook(Request $request)
    {
        try {
            Log::info('Payment webhook received', $request->all());

            $payment = Payment::where('id', $request->merchant_id)->firstOrFail();


            $newStatus = $request->status === 'successed' ? 1 : 2;

            $payment->update([
                'status' => $newStatus
            ]);

            // Если платеж успешный, можно добавить доп. логику
            if ($newStatus === 1) {
                NotificationsServiceFacade::sendDepositConfirmation($payment);
            }

            return response()->json(['status' => 'ok']);
        } catch (Exception $e) {
            Log::error('Webhook processing failed', [
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);

            return response()->json([
                'error' => true,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
