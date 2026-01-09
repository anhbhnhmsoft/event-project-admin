<?php

namespace App\Observers;

use App\Models\Membership;

class MembershipObserver
{
    /**
     * Handle the Membership "created" event.
     */
    public function created(Membership $membership): void
    {
        $this->syncToRevenueCat($membership);
    }

    /**
     * Handle the Membership "updated" event.
     */
    public function updated(Membership $membership): void
    {
        if ($membership->wasChanged(['product_id', 'name', 'type'])) {
            $this->syncToRevenueCat($membership);
        }
    }

    /**
     * Sync membership to RevenueCat
     */
    private function syncToRevenueCat(Membership $membership): void
    {
        // Only sync Customer memberships with product_id
        if ((int)$membership->type !== \App\Utils\Constants\MembershipType::FOR_CUSTOMER->value || empty($membership->product_id)) {
            return;
        }

        try {
            $service = app(\App\Services\RevenueCatService::class);
            $result = $service->syncMembership($membership);

            if ($result['status']) {
                $this->sendNotification('success', 'RevenueCat Sync: ' . $result['message']);
            } else {
                $this->sendNotification('warning', 'RevenueCat Sync Failed: ' . $result['message']);
            }
        } catch (\Exception $e) {
            $this->sendNotification('danger', 'RevenueCat Sync Error: ' . $e->getMessage());
        }
    }

    private function sendNotification(string $status, string $message): void
    {
        // Check if running in Filament/Web context to avoid errors in CLI/Queue
        if (class_exists(\Filament\Notifications\Notification::class) && request()->hasHeader('X-Inertia') === false) {
            // Basic Filament notification if applicable
            try {
                \Filament\Notifications\Notification::make()
                    ->title($message)
                    ->$status()
                    ->send();
            } catch (\Exception $e) {
                // Ignore if notification fails (e.g. CLI)
            }
        }
    }

    /**
     * Handle the Membership "deleted" event.
     */
    public function deleted(Membership $membership): void
    {
        //
    }

    /**
     * Handle the Membership "restored" event.
     */
    public function restored(Membership $membership): void
    {
        //
    }

    /**
     * Handle the Membership "force deleted" event.
     */
    public function forceDeleted(Membership $membership): void
    {
        //
    }
}
