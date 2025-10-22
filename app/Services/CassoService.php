<?php

namespace App\Services;

use App\Utils\Constants\CommonConstant;
use App\Utils\Constants\ConfigName;
use App\Utils\Constants\TransactionType;
use App\Utils\Helper;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CassoService
{
    /**
     * @throws ConnectionException
     */
    public function registerPaymentRequest(array $payload, Carbon $expiredAt, int $typeTrans)
    {
        $user = Auth::user();
        $organizerId = match ($typeTrans) {
            TransactionType::PLAN_SERVICE->value => 1,
            TransactionType::MEMBERSHIP->value => $user->organizer_id,
            TransactionType::EVENT_SEAT->value => $user->organizer_id,
            default => null,
        };

        if (!$organizerId) {
            Log::warning('CassoService: Organizer ID not found for transaction type', [
                'type' => $typeTrans,
                'user_id' => $user?->id,
            ]);

            return [
                'status' => false,
                'message' => 'Không xác định được tổ chức để tạo giao dịch thanh toán.',
            ];
        }
        $config = app(ConfigService::class);
        $signature = Helper::generateSignature(
            data: $payload,
            key: $config->getConfigValue(ConfigName::CHECKSUM_KEY->value, $organizerId)
        );
        $response = Http::withHeaders([
            'X-Client-Id' => $config->getConfigValue(ConfigName::CLIENT_ID_APP->value, $organizerId),
            'X-Api-Key'   => $config->getConfigValue(ConfigName::API_KEY->value, $organizerId),
            'Content-Type' => 'application/json',
        ])->timeout(15)
            ->post(CommonConstant::PAYOS_URL . 'payment-requests', array_merge($payload, [
                'expiredAt' => $expiredAt->timestamp,
                'signature' => $signature
            ]));
        return json_decode($response->getBody()->getContents(), true);
    }
}
