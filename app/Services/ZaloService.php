<?php

namespace App\Services;

use App\Models\ZaloToken;
use App\Utils\Constants\PurposeZNS;
use App\Utils\Helper;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Zalo\Zalo;
use App\Utils\Constants\ZaloEndPointExtends;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ZaloService
{
    private Zalo $zalo;
    private string $oaId;
    private string $appId;
    private string $appSecret;

    public function __construct()
    {
        $this->appId = config('services.zalo.app_id');
        $this->appSecret = config('services.zalo.app_secret');
        $this->oaId = config('services.zalo.oa_id');

        // Validate required configs
        if (empty($this->appId) || empty($this->appSecret) || empty($this->oaId)) {
            throw new \RuntimeException(
                'Zalo configuration is missing. Please check services.zalo config.'
            );
        }

        $this->zalo = new Zalo([
            'app_id' => $this->appId,
            'app_secret' => $this->appSecret,
        ]);
    }

    /**
     * Gửi OTP qua Zalo ZNS
     *
     * @param string $phone - Số điện thoại
     * @param string $otp - Mã OTP 6 số
     * @param PurposeZNS $purpose - Mục đích gửi OTP
     * @return array
     */
    public function sendOTP(string $phone, string $otp, PurposeZNS $purpose): array
    {
        try {
            // Format phone number
            $formattedPhone = Helper::formatPhone($phone);

            // Validate phone
            if (!Helper::isValidPhone($phone)) {
                return [
                    'success' => false,
                    'message' => __('error.unvalid_phonenumber')
                ];
            }

            // Get access token
            $accessToken = $this->getAccessToken();
            if (!$accessToken) {
                return [
                    'success' => false,
                    'message' => __('error.couldnot_get_token_zalo')
                ];
            }

            // Get template ID
            $templateId = $this->getTemplateId($purpose);

            // Prepare template data
            $templateData = [
                'otp' => $otp,
            ];

            // Prepare request params
            $params = [
                'phone' => $formattedPhone,
                'template_id' => $templateId,
                'template_data' => $templateData,
                'tracking_id' => uniqid('otp_', true),
            ];

            // Send ZNS via HTTP request
            $response = Http::withHeaders([
                'access_token' => $accessToken,
                'Content-Type' => 'application/json',
            ])
                ->post(ZaloEndPointExtends::API_OA_SEND_ZNS, $params);

            $responseData = $response->json();

            // Check response
            if (isset($responseData['error']) && $responseData['error'] !== 0) {
                Log::error('ZaloService::sendOTP failed', [
                    'phone' => $formattedPhone,
                    'error_code' => $responseData['error'],
                    'error_message' => $responseData['message'] ?? 'Unknown error',
                ]);

                $errorCode = $responseData['error'];
                $errorMessage = $responseData['message'] ?? 'Unknown error';

                // Xử lý cụ thể cho trường hợp không có Zalo
                if ($errorCode == -212) {
                    return [
                        'success' => false,
                        'is_zalo_user' => false, // Flag để bên ngoài biết và gọi SMS thay thế
                        'message' => __('error.not_zalo_user'),
                    ];
                }

                if ($errorCode == -213) {
                    return [
                        'success' => false,
                        'is_blocked' => true,
                        'message' => __('error.user_blocked'),
                    ];
                }

                return [
                    'success' => false,
                    'message' => __('error.couldnot_send_otp'),
                    'error_code' => $responseData['error'],
                ];
            }

            Log::info('ZaloService::sendOTP success', [
                'phone' => $formattedPhone,
                'purpose' => $purpose->name,
                'msg_id' => $responseData['data']['msg_id'] ?? null,
            ]);

            return [
                'success' => true,
                'message' => __('error.send_otp_success'),
                'data' => $responseData['data'] ?? [],
            ];
        } catch (HttpException $e) {
            Log::error('ZaloService::sendOTP HttpException', [
                'phone' => $phone,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        } catch (\Throwable $th) {
            Log::error('ZaloService::sendOTP Exception', [
                'phone' => $phone,
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => __('error.couldnot_send_otp')
            ];
        }
    }

    /**
     * Refresh Zalo access token
     *
     * @param string|null $refreshToken - Refresh token from Zalo (optional, will use from DB if not provided)
     * @return array
     */
    public function refreshAccessToken(?string $refreshToken = null): array
    {
        return Cache::lock('zalo_refresh_token_lock', 10)->block(5, function () use ($refreshToken) {
            try {
                $tokenRecord = ZaloToken::getLatest();

                if (!$refreshToken) {
                    if (!$tokenRecord || !$tokenRecord->refresh_token) {
                        return [
                            'success' => false,
                            'message' => 'No refresh token available',
                        ];
                    }

                    if (!$tokenRecord->willExpireSoon()) {
                        Log::info('ZaloService::refreshAccessToken - Token already refreshed by another process. Skipping.');
                        return [
                            'success' => true,
                            'access_token' => $tokenRecord->access_token,
                            'refresh_token' => $tokenRecord->refresh_token,
                            'expires_in' => $tokenRecord->expired_at - time(),
                        ];
                    }

                    $refreshToken = $tokenRecord->refresh_token;
                }

                // 2. Perform the Refresh Request
                $response = Http::withHeaders([
                    'secret_key' => $this->appSecret,
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ])->asForm()->post(ZaloEndPointExtends::API_OA_ACCESS_TOKEN, [
                    'app_id' => $this->appId,
                    'grant_type' => 'refresh_token',
                    'refresh_token' => $refreshToken,
                ]);

                $data = $response->json();

                if (isset($data['error']) && $data['error'] !== 0) {
                    Log::error('ZaloService::refreshAccessToken failed', [
                        'error' => $data['error'],
                        'message' => $data['message'] ?? 'Unknown error',
                    ]);

                    return [
                        'success' => false,
                        'message' => $data['message'] ?? 'Refresh token thất bại',
                    ];
                }

                $accessToken = $data['access_token'];
                $newRefreshToken = $data['refresh_token'] ?? $refreshToken;
                $expiresIn = $data['expires_in'] ?? 3600;

                // 3. Save to database
                ZaloToken::createOrUpdate($accessToken, $newRefreshToken, $expiresIn);

                // 4. Cache (slightly less time than actual expiry)
                $cacheExpiry = now()->addSeconds($expiresIn - 300); // -5 minutes for safety
                Cache::put('zalo_access_token', $accessToken, $cacheExpiry);
                Cache::put('zalo_refresh_token', $newRefreshToken, now()->addDays(90));

                Log::info('ZaloService::refreshAccessToken success', [
                    'expires_in' => $expiresIn,
                ]);

                return [
                    'success' => true,
                    'access_token' => $accessToken,
                    'refresh_token' => $newRefreshToken,
                    'expires_in' => $expiresIn,
                ];
            } catch (\Throwable $th) {
                Log::error('ZaloService::refreshAccessToken Exception', [
                    'error' => $th->getMessage(),
                ]);

                return [
                    'success' => false,
                    'message' => 'Có lỗi xảy ra: ' . $th->getMessage(),
                ];
            }
        });
    }

    /**
     * Get access token from cache or database, refresh if expired
     *
     * @return string|null
     */
    private function getAccessToken(): ?string
    {
        // Step 1: Try to get from cache (fastest)
        $cachedToken = Cache::get('zalo_access_token');
        if ($cachedToken) {
            return $cachedToken;
        }

        // Step 2: Get from database
        $tokenRecord = ZaloToken::getLatest();

        if (!$tokenRecord) {
            Log::error('ZaloService::getAccessToken - No token record in database');
            return null;
        }

        // Step 3: Check if token is still valid
        if (!$tokenRecord->willExpireSoon()) {
            // Token is still valid, cache it and return
            $remainingTime = $tokenRecord->expired_at - time();
            // Cache if remaining time is significant enough (> 5 minutes)
            if ($remainingTime > 300) {
                Cache::put('zalo_access_token', $tokenRecord->access_token, now()->addSeconds($remainingTime - 300));
            }
            return $tokenRecord->access_token;
        }

        // Step 4: Token expired or will expire soon, refresh it
        Log::info('ZaloService::getAccessToken - Token expired or expiring soon, refreshing...');

        $result = $this->refreshAccessToken($tokenRecord->refresh_token);

        return $result['success'] ? $result['access_token'] : null;
    }

    /**
     * Get template ID based on purpose
     *
     * @param PurposeZNS $purpose
     * @return string
     */
    private function getTemplateId(PurposeZNS $purpose): string
    {
        $templates = config('services.zalo.otp_templates');

        return match ($purpose) {
            PurposeZNS::REGISTER => $templates['register'] ?? $templates['otp'],
            PurposeZNS::FORGOT_PASSWORD => $templates['forgot_password'] ?? $templates['otp'],
            PurposeZNS::VERIFY_PHONE => $templates['verify_phone'] ?? $templates['otp'],
            default => $templates['otp'],
        };
    }

    /**
     * Set access token manually (for initial setup)
     *
     * @param string $accessToken
     * @param string $refreshToken
     * @param int $expiresIn
     * @return void
     */
    public function setTokens(string $accessToken, string $refreshToken, int $expiresIn = 3600): void
    {
        // Save to database
        ZaloToken::createOrUpdate($accessToken, $refreshToken, $expiresIn);

        // Also cache for quick access
        Cache::put('zalo_access_token', $accessToken, now()->addSeconds($expiresIn - 300));
        Cache::put('zalo_refresh_token', $refreshToken, now()->addDays(90));

        Log::info('ZaloService::setTokens - Tokens saved to database and cached successfully');
    }

    /**
     * Get Zalo Authorization URL
     *
     * @param string $callbackUrl
     * @param string $state
     * @return string
     */
    public function getAuthorizationUrl(string $callbackUrl, string $state = ''): string
    {
        $queryParams = http_build_query([
            'app_id' => $this->appId,
            'redirect_uri' => $callbackUrl,
            'state' => $state,
        ]);

        return "https://oauth.zaloapp.com/oa/permission?{$queryParams}";
    }

    /**
     * Get Access Token from Authorization Code
     *
     * @param string $code
     * @return array
     */
    public function getAccessTokenFromCode(string $code): array
    {
        try {
            $response = Http::withHeaders([
                'secret_key' => $this->appSecret,
                'Content-Type' => 'application/x-www-form-urlencoded',
            ])->asForm()->post(ZaloEndPointExtends::API_OA_ACCESS_TOKEN, [ // Endpoint for access token is same base, but let's verify if ZaloEndPointExtends has it. Actually API_REFRESH_TOKEN is https://oauth.zaloapp.com/v4/access_token which is correct for both.
                'app_id' => $this->appId,
                'grant_type' => 'authorization_code',
                'code' => $code,
            ]);

            $data = $response->json();

            if (isset($data['error']) && $data['error'] !== 0) {
                Log::error('ZaloService::getAccessTokenFromCode failed', [
                    'error' => $data['error'],
                    'message' => $data['message'] ?? 'Unknown error',
                ]);

                return [
                    'success' => false,
                    'message' => $data['message'] ?? 'Lấy access token thất bại',
                ];
            }

            $accessToken = $data['access_token'];
            $refreshToken = $data['refresh_token'];
            $expiresIn = $data['expires_in'];

            // Store tokens
            $this->setTokens($accessToken, $refreshToken, $expiresIn);

            return [
                'success' => true,
                'access_token' => $accessToken,
                'refresh_token' => $refreshToken,
                'expires_in' => $expiresIn,
            ];
        } catch (\Throwable $th) {
            Log::error('ZaloService::getAccessTokenFromCode Exception', [
                'error' => $th->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Exception: ' . $th->getMessage(),
            ];
        }
    }
}
