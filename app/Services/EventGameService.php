<?php

namespace App\Services;

use App\Models\EventGame;
use App\Models\EventGameGift;
use App\Models\EventUserGift;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Jobs\SendNotifications;
use App\Utils\Constants\RoleUser;
use App\Utils\DTO\NotificationPayload;
use App\Utils\Constants\UserNotificationType;
use Carbon\Carbon;

class EventGameService
{
    public function eventUserGiftPagination(array $filters = [], int $page = 1, int $limit = 10)
    {
        try {
            return EventUserGift::filter($filters)->orderBy('created_at', 'desc')
                ->paginate(perPage: $limit, page: $page);
        } catch (\Exception $e) {
            return new LengthAwarePaginator([], 0, $limit, $page);
        }
    }

    public function getDetailGameEvent($id): array
    {
        try {
            $game = EventGame::query()
                ->with(['gifts', 'event'])
                ->find($id);

            if (!$game) {
                return [
                    'status' => false,
                    'message' => __('common.common_error.data_not_found'),
                ];
            }

            return [
                'status' => true,
                'game' => $game,
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => __('common.common_error.server_error'),
            ];
        }
    }

    public function getHistoryGameEvent($gameId, int $perPage = 10): array
    {
        try {
            $game = EventGame::find($gameId);

            if (!$game) {
                return [
                    'status'  => false,
                    'message' => __('common.common_error.data_not_found'),
                ];
            }

            $histories = EventUserGift::with([
                'user:id,name,email,avatar_path',
                'gift:id,event_game_id,name,description,image'
            ])
                ->whereHas('gift', function ($q) use ($gameId) {
                    $q->where('event_game_id', $gameId);
                })
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            return [
                'status' => true,
                'data'   => $histories
            ];
        } catch (\Exception $e) {
            Log::info($e->getMessage());
            return [
                'status'  => false,
                'message' => __('common.common_error.server_error'),
            ];
        }
    }

    public function getGiftsOfGame($gameId): array
    {
        try {
            $gifts = EventGameGift::query()
                ->where('event_game_id', $gameId)
                ->orderBy('created_at', 'desc')
                ->get();

            return [
                'status' => true,
                'data'   => $gifts,
            ];
        } catch (\Exception $e) {
            return [
                'status'  => false,
                'message' => __('common.common_error.server_error'),
            ];
        }
    }

    public function getEligibleUsers($game, int $perPage = 20): array
    {
        try {
            $customRates = $game->config_game['custom_user_rates'] ?? [];
            if (empty($customRates)) {
                return [
                    'status' => false,
                    'message' => __('game.error.no_players_configured'),
                ];
            }

            $userIds = collect($customRates)
                ->pluck('user_id')
                ->filter()
                ->unique()
                ->values();

            if ($userIds->isEmpty()) {
                return [
                    'status' => false,
                    'message' => __('game.error.no_valid_players'),
                ];
            }

            $users = \App\Models\User::whereIn('id', $userIds)
                ->paginate($perPage);

            return [
                'status' => true,
                'data' => $users,
            ];
        } catch (\Exception $e) {
            Log::debug($e->getMessage());
            return [
                'status' => false,
                'message' => __('common.common_error.server_error'),
            ];
        }
    }

    public function createGiftHistory(int $userId, int $giftId): array
    {
        DB::beginTransaction();
        try {
            $gift = EventGameGift::find($giftId);

            if (!$gift) {
                return [
                    'status'  => false,
                    'message' => __('game.error.gift_not_found'),
                ];
            }

            if ($gift->quantity <= 0) {
                return [
                    'status'  => false,
                    'message' => __('game.error.gift_out_of_stock'),
                ];
            }

            $history = EventUserGift::create([
                'user_id'            => $userId,
                'event_game_gift_id' => $giftId,
            ]);

            $gift->decrement('quantity');

            $history->load([
                'user:id,name,email,avatar_path',
                'gift:id,event_game_id,name,description,image'
            ]);

            DB::commit();

            return [
                'status'  => true,
                'message' => __('game.success.gift_history_saved'),
                'data'    => $history,
            ];
        } catch (\Throwable $e) {
            Log::debug($e->getMessage());
            DB::rollBack();
            return [
                'status'  => false,
                'message' => __('common.common_error.server_error'),
            ];
        }
    }


    public function checkGameAccess($game, $user): bool
    {
        if (!$game || !$game->event) {
            return false;
        }

        if ($user->role == RoleUser::SUPER_ADMIN->value) {
            return true;
        }

        return $game->event->organizer_id === $user->organizer_id;
    }

    /**
     * Initiate spin - calculate prize based on rates and cache the result
     * Returns spin_id for later retrieval, NOT the actual prize
     */
    public function initiateSpin($gameId, $userId)
    {
        $game = EventGame::with('gifts')->find($gameId);

        if (!$game) {
            return ['status' => false, 'message' => __('game.error.game_not_found')];
        }

        // Get gifts with rates for this user
        $customRates = collect($game->config_game['custom_user_rates'] ?? []);
        $userRate = $customRates->firstWhere('user_id', $userId);

        if ($userRate && !empty($userRate['rates'])) {
            $gifts = collect($userRate['rates'])
                ->map(function ($r) use ($game) {
                    $gift = $game->gifts->firstWhere('id', $r['gift_id']);
                    if ($gift && $gift->quantity > 0) {
                        $gift->rate = $r['rate'];
                        return $gift;
                    }
                    return null;
                })
                ->filter();
        } else {
            $gifts = $game->gifts->filter(fn($g) => $g->quantity > 0 && $g->rate > 0);
        }

        if ($gifts->isEmpty()) {
            return ['status' => false, 'message' => __('game.error.no_valid_gifts')];
        }

        // Calculate winning gift based on rates
        $totalRate = $gifts->sum('rate');
        $random = mt_rand(1, $totalRate);
        $cumulative = 0;
        $selectedGift = null;

        foreach ($gifts as $gift) {
            $cumulative += $gift->rate;
            if ($random <= $cumulative) {
                $selectedGift = $gift;
                break;
            }
        }

        if (!$selectedGift) {
            return ['status' => false, 'message' => __('game.error.cannot_select_gift')];
        }

        // Generate unique spin ID and cache the result for 60 seconds
        $spinId = uniqid('spin_', true);
        $cacheKey = "game_spin_{$gameId}_{$userId}_{$spinId}";

        cache()->put($cacheKey, [
            'gift_id' => $selectedGift->id,
            'user_id' => $userId,
            'game_id' => $gameId,
        ], now()->addSeconds(60));

        return [
            'status' => true,
            'spin_id' => $spinId,
            'gift_id' => (string) $selectedGift->id,
            'gift' => $selectedGift,
            'wheel_items' => $gifts->map(fn($g) => ['id' => (string) $g->id, 'name' => $g->name])->values(),
        ];
    }

    /**
     * Reveal prize - retrieve cached result and save to history
     * Called after wheel animation completes
     */
    public function revealPrize($gameId, $userId, $spinId)
    {
        $cacheKey = "game_spin_{$gameId}_{$userId}_{$spinId}";
        $cachedResult = cache()->get($cacheKey);

        if (!$cachedResult) {
            return ['status' => false, 'message' => __('game.error.spin_expired')];
        }

        // Delete cache to prevent reuse
        cache()->forget($cacheKey);

        $gift = EventGameGift::find($cachedResult['gift_id']);
        if (!$gift) {
            return ['status' => false, 'message' => __('game.error.gift_not_found')];
        }

        // Save history
        $history = $this->createGiftHistory($userId, $gift->id);
        if (!$history['status']) {
            return ['status' => false, 'message' => $history['message']];
        }

        // Send notification
        try {
            $game = EventGame::find($gameId);
            $payload = new NotificationPayload(
                title: __('event.success.congratulartion_prize'),
                description: __('event.success.congratulartion_desc', ['gift_name' => $gift->name, 'game' => $game->name]),
                data: [
                    'game_id' => $gameId,
                    'gift_id' => $gift->id,
                    'history_id' => $history['data']->id,
                ],
                notificationType: UserNotificationType::SYSTEM_ANNOUNCEMENT,
            );
            SendNotifications::dispatch($payload, [$userId])->delay(now()->addSeconds(2))->onQueue('notifications');
        } catch (\Throwable $e) {
            Log::error('EventGameService::revealPrize - Notification failed', [
                'error' => $e->getMessage(),
                'user_id' => $userId,
                'game_id' => $gameId,
            ]);
        }

        return [
            'status' => true,
            'gift' => $gift,
            'history' => $history['data'],
        ];
    }

    /**
     * @deprecated Use initiateSpin and revealPrize instead
     */
    public function spinPrize($gameId, $userId)
    {
        // Keep for backward compatibility, but log warning
        Log::warning('EventGameService::spinPrize is deprecated, use initiateSpin/revealPrize instead');

        $initResult = $this->initiateSpin($gameId, $userId);
        if (!$initResult['status']) {
            return $initResult;
        }

        return $this->revealPrize($gameId, $userId, $initResult['spin_id']);
    }

    public function updateGameEvent(EventGame $record, $data)
    {

        try {
            $record->update([
                'name' => $data['name'],
                'description' => $data['description'],
                'game_type' => $data['game_type'],
                'config_game' => $data['config_game'],
            ]);

            return [
                'status' => true,
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            return [
                'status' => false,
            ];
        }
    }
}
