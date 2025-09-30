<?php

namespace App\Services;

use App\Utils\Constants\CommonConstant;
use App\Utils\Constants\ConfigName;
use App\Utils\Helper;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

class CassoService
{
    /**
     * @throws ConnectionException
     */
    public function registerPaymentRequest(array $payload, Carbon $expiredAt)
    {
        $config = app(ConfigService::class);
        $signature = Helper::generateSignature(
            data: $payload,
            key: $config->getConfigValue(ConfigName::CHECKSUM_KEY->value)
        );
        $response = Http::withHeaders([
            'X-Client-Id' => $config->getConfigValue(ConfigName::CLIENT_ID_APP->value),
            'X-Api-Key'   => $config->getConfigValue(ConfigName::API_KEY->value),
            'Content-Type' => 'application/json',
        ])->timeout(15)
            ->post(CommonConstant::PAYOS_URL .'payment-requests', array_merge($payload, [
                'expiredAt' => $expiredAt->timestamp,
                'signature' => $signature
            ]));
        return json_decode($response->getBody()->getContents(), true);
    }
}
