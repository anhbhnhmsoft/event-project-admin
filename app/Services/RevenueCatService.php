<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class RevenueCatService
{
    private string $apiKey;
    private string $webhookSecret;
    private string $projectId;
    private const API_V2_URL = 'https://api.revenuecat.com/v2';

    public function __construct()
    {
        $this->apiKey = config('services.revenuecat.api_key');
        $this->webhookSecret = config('services.revenuecat.webhook_secret');
        $this->projectId = config('services.revenuecat.project_id');
    }

    /**
     * Verify webhook signature from RevenueCat
     *
     * @param string $payload Raw request body
     * @param string $signature Signature from X-Revenuecat-Signature header
     * @return bool
     */
    public function verifyWebhookSignature(string $payload, string $signature): bool
    {
        if (empty($this->webhookSecret)) {
            Log::warning('RevenueCat webhook secret is not configured');
            return false;
        }

        $expectedSignature = hash_hmac('sha256', $payload, $this->webhookSecret);
        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Get subscriber info from RevenueCat API
     *
     * @param string $appUserId User ID in your system
     * @return array
     */
    public function getSubscriberInfo(string $appUserId): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])
                ->timeout(15)
                ->get("https://api.revenuecat.com/v1/subscribers/{$appUserId}");

            if ($response->successful()) {
                return [
                    'status' => true,
                    'data' => $response->json()
                ];
            }

            Log::error('RevenueCat API error', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return [
                'status' => false,
                'message' => 'Failed to fetch subscriber info'
            ];
        } catch (\Exception $e) {
            Log::error('RevenueCat API exception', [
                'error' => $e->getMessage()
            ]);

            return [
                'status' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Sync membership configuration to RevenueCat
     *
     * @param \App\Models\Membership $membership
     * @return array
     */
    public function syncMembership(\App\Models\Membership $membership): array
    {
        if (empty($this->projectId)) {
            Log::warning('RevenueCat Project ID is missing. Cannot sync membership.');
            return ['status' => false, 'message' => 'Project ID not configured'];
        }

        if (empty($membership->product_id)) {
            return ['status' => false, 'message' => 'Membership has no Product ID'];
        }

        try {
            Log::info("Starting RevenueCat Sync for Membership: {$membership->id}");

            // 1. Ensure Product Exists
            $rcProductId = $this->ensureProductExists($membership);
            if (!$rcProductId) {
                // If failed, detailed log is already in ensureProductExists
                throw new \Exception("Failed to ensure product exists (check logs).");
            }

            // 2. Ensure Entitlement Exists
            $entitlementId = $this->ensureEntitlementExists($membership);
            if (!$entitlementId) {
                throw new \Exception("Failed to ensure entitlement exists.");
            }

            // 3. Attach Product to Entitlement
            $attached = $this->attachProductToEntitlement($rcProductId, $entitlementId);
            if (!$attached) {
                Log::warning("Could not attach product to entitlement (might already be attached or API error)");
            }

            // 4. Ensure Package in Offering
            // Default offering usually has lookup_key 'default'
            $offeringId = $this->ensureOfferingExists('default', 'Default Offering');
            if ($offeringId) {
                $this->createPackageInOffering($offeringId, $membership->product_id, $rcProductId, $membership->name);
            }

            return ['status' => true, 'message' => 'Synced successfully to RevenueCat'];
        } catch (\Exception $e) {
            Log::error('RevenueCat Sync Error', ['error' => $e->getMessage()]);
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    // --- Helper Methods using RevenueCat V2 API ---

    private function makeRequest(string $method, string $endpoint, array $data = []): array
    {
        $url = self::API_V2_URL . "/projects/{$this->projectId}/" . $endpoint;

        // Use secret API Key for Bearer token
        $response = Http::withToken($this->apiKey)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->send($method, $url, empty($data) ? [] : ['json' => $data]);

        if ($response->successful()) {
            return ['status' => true, 'data' => $response->json()];
        }

        // Handling 409 Conflict (e.g. already exists) is common, so return status false but with data
        if ($response->status() === 409) {
            return ['status' => false, 'code' => 409, 'message' => 'Resource already exists'];
        }

        Log::error("RC API Error: $method $endpoint", [
            'status' => $response->status(),
            'body' => $response->body()
        ]);

        return ['status' => false, 'code' => $response->status(), 'message' => $response->body()];
    }

    private function ensureProductExists(\App\Models\Membership $membership): ?string
    {
        // Try to create directly
        $payload = [
            'type' => 'subscription', // Assuming subscription for memberships
            'store_identifier' => $membership->product_id,
            'display_name' => $membership->name,
        ];

        $result = $this->makeRequest('POST', 'products', $payload);

        if ($result['status']) {
            return $result['data']['id'] ?? null;
        }

        if (($result['code'] ?? 0) === 409) {
            // Already exists, we need to fetch it to get the ID.
            return $this->findResourceId('products', 'store_identifier', $membership->product_id);
        }

        Log::error("Failed to create product: " . ($result['message'] ?? 'Unknown error'));
        return null; // Fail
    }

    private function ensureEntitlementExists(\App\Models\Membership $membership): ?string
    {
        // Use a slug based on membership name or a fixed pattern.
        $lookupKey = Str::slug($membership->name, '_');

        $payload = [
            'lookup_key' => $lookupKey,
            'display_name' => $membership->name . ' Access',
        ];

        $result = $this->makeRequest('POST', 'entitlements', $payload);

        if ($result['status']) {
            return $result['data']['id'] ?? null;
        }

        if (($result['code'] ?? 0) === 409) {
            return $this->findResourceId('entitlements', 'lookup_key', $lookupKey);
        }

        return null; // Fail
    }

    private function attachProductToEntitlement(string $productId, string $entitlementId): bool
    {
        // Url: /entitlements/{entitlement_id}/products
        // Post body: { "product_id": "rc_product_id" }

        $result = $this->makeRequest('POST', "entitlements/{$entitlementId}/products", [
            'product_id' => $productId
        ]);

        if ($result['status']) return true;
        if (($result['code'] ?? 0) === 409) return true;

        return false;
    }

    private function ensureOfferingExists(string $lookupKey, string $displayName): ?string
    {
        $payload = [
            'lookup_key' => $lookupKey,
            'display_name' => $displayName
        ];

        $result = $this->makeRequest('POST', 'offerings', $payload);

        if ($result['status']) {
            return $result['data']['id'] ?? null;
        }

        if (($result['code'] ?? 0) === 409) {
            return $this->findResourceId('offerings', 'lookup_key', $lookupKey);
        }

        return null;
    }

    private function createPackageInOffering(string $offeringId, string $packageLookupKey, string $productId, string $displayName): bool
    {
        // Url: /offerings/{offering_id}/packages
        $payload = [
            'lookup_key' => $packageLookupKey,
            'display_name' => $displayName,
            'product_id' => $productId
        ];

        $result = $this->makeRequest('POST', "offerings/{$offeringId}/packages", $payload);

        return $result['status'] || ($result['code'] ?? 0) === 409;
    }

    private function findResourceId(string $resourceType, string $searchField, string $value): ?string
    {
        // List resources and filter. 
        $result = $this->makeRequest('GET', $resourceType . '?limit=20');

        if (!$result['status'] || !isset($result['data']['items'])) {
            return null;
        }

        foreach ($result['data']['items'] as $item) {
            if (isset($item[$searchField]) && $item[$searchField] === $value) {
                return $item['id'];
            }
        }

        return null;
    }
}
