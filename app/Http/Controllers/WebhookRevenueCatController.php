<?php

namespace App\Http\Controllers;

use App\Models\MembershipUser;
use App\Services\MemberShipService;
use App\Services\RevenueCatService;
use App\Utils\Constants\MembershipUserStatus;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookRevenueCatController extends Controller
{
    private RevenueCatService $revenueCatService;
    private MemberShipService $membershipService;

    public function __construct(
        RevenueCatService $revenueCatService,
        MemberShipService $membershipService
    ) {
        $this->revenueCatService = $revenueCatService;
        $this->membershipService = $membershipService;
    }

    public function handle(Request $request): JsonResponse
    {
        Log::info('RevenueCat Webhook received', $request->all());

        // Verify webhook signature
        $signature = $request->header('X-Revenuecat-Signature');
        $payload = $request->getContent();

        if (!$signature || !$this->revenueCatService->verifyWebhookSignature($payload, $signature)) {
            Log::warning('Invalid RevenueCat webhook signature');
            return response()->json([
                'message' => __('common.common_error.invalid_signature')
            ], 401);
        }

        $data = $request->all();
        $event = $data['event'] ?? null;

        if (!$event || !isset($event['type'])) {
            Log::warning('Invalid RevenueCat webhook payload', ['data' => $data]);
            return response()->json([
                'message' => __('common.common_error.invalid_webhook_payload')
            ], 400);
        }

        // Handle different event types
        return match ($event['type']) {
            'INITIAL_PURCHASE', 'RENEWAL', 'NON_RENEWING_PURCHASE' => $this->handlePurchase($event),
            'CANCELLATION', 'EXPIRATION' => $this->handleExpiration($event),
            default => $this->handleUnknownEvent($event['type'])
        };
    }

    private function handlePurchase(array $event): JsonResponse
    {
        $appUserId = $event['app_user_id'] ?? null;
        $productId = $event['product_id'] ?? null;
        $transactionId = $event['id'] ?? null;
        $purchasedAtMs = $event['purchased_at_ms'] ?? null;
        $expirationAtMs = $event['expiration_at_ms'] ?? null;

        if (!$appUserId || !$productId || !$transactionId) {
            Log::warning('Missing required fields in purchase event', ['event' => $event]);
            return response()->json([
                'message' => __('common.common_error.data_not_fields')
            ], 400);
        }

        try {
            $purchaseDate = Carbon::createFromTimestampMs($purchasedAtMs);
            $expirationDate = $expirationAtMs ? Carbon::createFromTimestampMs($expirationAtMs) : null;

            $result = $this->membershipService->activateMembershipFromIAP(
                userId: (int)$appUserId,
                membershipSku: $productId,
                transactionId: $transactionId,
                purchaseDate: $purchaseDate,
                expirationDate: $expirationDate
            );

            if ($result['status']) {
                return response()->json(['message' => __('common.common_success.update_success')], 200);
            }

            return response()->json([
                'message' => $result['message']
            ], 400);
        } catch (\Exception $e) {
            Log::error('Error processing purchase event', [
                'error' => $e->getMessage(),
                'event' => $event
            ]);

            return response()->json([
                'message' => __('common.common_error.server_error')
            ], 500);
        }
    }

    private function handleExpiration(array $event): JsonResponse
    {
        $appUserId = $event['app_user_id'] ?? null;

        if (!$appUserId) {
            return response()->json([
                'message' => __('common.common_error.data_not_fields')
            ], 400);
        }

        try {
            // Update membership status to expired
            $updated = MembershipUser::where('user_id', $appUserId)
                ->where('status', MembershipUserStatus::ACTIVE->value)
                ->update(['status' => MembershipUserStatus::EXPIRED->value]);

            Log::info('Membership expired from RevenueCat', [
                'user_id' => $appUserId,
                'updated_count' => $updated
            ]);

            return response()->json(['message' => __('common.common_success.update_success')], 200);
        } catch (\Exception $e) {
            Log::error('Error processing expiration event', [
                'error' => $e->getMessage(),
                'event' => $event
            ]);

            return response()->json([
                'message' => __('common.common_error.server_error')
            ], 500);
        }
    }

    private function handleUnknownEvent(string $eventType): JsonResponse
    {
        Log::info('Unhandled RevenueCat event type', ['type' => $eventType]);
        return response()->json(['message' => 'Event type not handled'], 200);
    }
}
