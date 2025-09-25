<?php

namespace App\Services;

use App\Models\Transactions;
use App\Utils\Constants\ConfigName;
use App\Utils\Constants\TransactionStatus;
use App\Utils\Constants\TransactionType;
use App\Utils\Helper;
use Exception;
use GuzzleHttp\Exception\ConnectException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CassoService
{   
    public const LINK = "https://api-merchant.payos.vn/v2/";
    public ConfigService $configService;

    public function processQrCode($transactionId): array
    {
        $transactionService = app(TransactionService::class);
        $transaction = $transactionService->getDetailTransaction($transactionId);
        if ($transaction['status'] && $transaction['transaction']) {

            $bankBin = $transaction['transaction']->config_pay['bin'];
            $bankAccountName =  $transaction['transaction']->config_pay['name'];
            $bankNumber =  $transaction['transaction']->config_pay['number'];
            $qrcode = Helper::generateQRCodeBanking($bankBin, $bankNumber, $bankAccountName, $transaction['transaction']->money, $transaction['transaction']->description);
            if ($bankNumber && $bankAccountName && $bankBin) {
                return [
                    'status' => true,
                    'qrcode' => $qrcode
                ];
            } else {
                return [
                    'status' => false,
                    'message' => __('common.common_error.server_error')
                ];
            }
        } else {
            return [
                'status' => false,
                'message' => __('common.common_error.server_error')
            ];
        }
    }


    public function registerNewTransaction(TransactionType $type, $amount, $foreignKey, $userId, $item = null)
    {
        $configService = app(ConfigService::class);
        $descBank = TransactionType::typeLabel($type->value) . Helper::getTimestampAsId();

        try {
            $orderCode = (int)(microtime(true) * 1000);
            $expiredAt = now()->addMinutes(10);
            $payload = [
                'amount' => $amount,
                'cancelUrl' => route('home'),
                'description' => $descBank,
                'orderCode' => $orderCode,
                'returnUrl' => route('home'),
            ];

            $signature = Helper::generateSignature($payload, $configService->getConfigValue(ConfigName::CHECKSUM_KEY->value));

            $response = Http::withHeaders([
                'X-Client-Id' => $configService->getConfigValue(ConfigName::CLIENT_ID_APP->value),
                'X-Api-Key'   => $configService->getConfigValue(ConfigName::API_KEY->value),
                'Content-Type' => 'application/json',
            ])->timeout(15)
                ->post(self::LINK .'payment-requests', array_merge($payload, [  
                    'expiredAt' => $expiredAt->timestamp,
                    'signature' => $signature
                ]));

            $responseData = json_decode($response->getBody()->getContents(), true);
            Log::error("PayOS API ", ['data' => $responseData['data'], 'desc' => $responseData['desc']]);
            if ($responseData['code'] !== '00') {
                Log::error("PayOS API error", ['code' => $responseData['code'], 'desc' => $responseData['desc']]);
                return [
                    'status' => false,
                    'message' => $responseData['desc'] ?? __('common.common_error.api_error'),
                ];
            }

            $transaction = Transactions::create([
                'type' => $type->value,
                'foreign_id' => $foreignKey,
                'money' => $amount,
                'transaction_code' => $orderCode,
                'description' => $descBank,
                'status' => TransactionStatus::WAITING->value,
                'metadata' => $responseData['data']['checkoutUrl'] ?? null,
                'user_id' => $userId,
                'transaction_id' => $item['id'] ?? $responseData['data']['paymentLinkId'] ?? null,
                'expired_at' => $expiredAt,
                'config_pay' => [
                    'name'   => $responseData['data']['accountName'],
                    'bin'    => $responseData['data']['bin'],
                    'number' => $responseData['data']['accountNumber']
                ]
            ]);

            return [
                'status' => true,
                'message' => __('common.common_success.get_success'),
                'data' => $transaction
            ];
        } catch (ConnectException $e) {
            Log::error("PayOS connection error", ['msg' => $e->getMessage()]);
            return [
                'status' => false,
                'message' => __('common.common_error.server_error'),
            ];
        } catch (\Exception $e) {
            Log::error("Unexpected error", ['msg' => $e->getMessage()]);
            return [
                'status' => false,
                'message' => __('common.common_error.server_error'),
            ];
        }
    }
}
