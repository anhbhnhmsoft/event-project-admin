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
use App\Utils\Constants\EventUserHistoryStatus;

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
            $eventId = $game->event_id ?? null;
            if (!$eventId) {
                return [
                    'status' => false,
                    'message' => __('common.common_error.data_not_found'),
                ];
            }

            $users = \App\Models\User::whereHas('eventUserHistories', function ($query) use ($eventId) {
                $query->where('event_id', $eventId)
                    ->where('status', \App\Utils\Constants\EventUserHistoryStatus::PARTICIPATED->value);
            })->paginate($perPage);

            if ($users->isEmpty()) {
                return [
                    'status' => false,
                    'message' => __('game.error.no_players'),
                ];
            }

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

    public function getCheckintUserEvent($game, int $perPage = 20): array
    {
        try {
            $eventId = $game->event_id ?? null;
            if (!$eventId) {
                return [
                    'status' => false,
                    'message' => __('common.common_error.data_not_found'),
                ];
            }

            // Get all gift IDs for this game
            $gameGiftIds = $game->gifts->pluck('id');

            // Get users who have won gifts in this game
            $winnersUserIds = EventUserGift::whereIn('event_game_gift_id', $gameGiftIds)
                ->pluck('user_id')
                ->unique();

            $users = \App\Models\User::whereHas('eventUserHistories', function ($query) use ($eventId) {
                $query->where('event_id', $eventId)
                    ->where('status', EventUserHistoryStatus::PARTICIPATED->value);
            })
                ->get()
                ->map(function ($user) use ($winnersUserIds) {
                    // Add dynamic attribute and make it visible for serialization
                    $user->setAttribute('has_received_gift', $winnersUserIds->contains($user->id));
                    $user->append('has_received_gift');
                    return $user;
                })
                ->sortBy('has_received_gift') // Non-winners first (false < true)
                ->values();

            // Paginate manually
            $total = $users->count();
            $page = request()->input('page', 1);
            $paginatedUsers = $users->forPage($page, $perPage);

            $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
                $paginatedUsers,
                $total,
                $perPage,
                $page,
                ['path' => request()->url(), 'query' => request()->query()]
            );

            return [
                'status' => true,
                'data' => $paginator,
            ];
        } catch (\Exception $e) {
            Log::error($e->getMessage());
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
     * Spin prize - calculate and award prize immediately
     * @deprecated The two-step flow (initiateSpin/revealPrize) is kept for backward compatibility
     */
    public function spinPrize($gameId, $userId)
    {
        $game = EventGame::with('gifts')->find($gameId);

        if (!$game) {
            return ['status' => false, 'message' => __('game.error.game_not_found')];
        }

        // Get gifts with quantity > 0 and rate > 0
        $gifts = $game->gifts->filter(fn($g) => $g->quantity > 0 && $g->rate > 0);

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

        // Save history immediately
        $history = $this->createGiftHistory($userId, $selectedGift->id);
        if (!$history['status']) {
            return ['status' => false, 'message' => $history['message']];
        }

        // Send notification
        try {
            $payload = new NotificationPayload(
                title: __('event.success.congratulartion_prize'),
                description: __('event.success.congratulartion_desc', ['gift_name' => $selectedGift->name, 'game' => $game->name]),
                data: [
                    'game_id' => $gameId,
                    'gift_id' => $selectedGift->id,
                    'history_id' => $history['data']->id,
                ],
                notificationType: UserNotificationType::SYSTEM_ANNOUNCEMENT,
            );
            SendNotifications::dispatch($payload, [$userId])->delay(now()->addSeconds(2))->onQueue('notifications');
        } catch (\Throwable $e) {
            Log::error('EventGameService::spinPrize - Notification failed', [
                'error' => $e->getMessage(),
                'user_id' => $userId,
                'game_id' => $gameId,
            ]);
        }

        return [
            'status' => true,
            'gift' => $selectedGift,
            'history' => $history['data'],
        ];
    }

    /**
     * @deprecated Use spinPrize instead
     */
    public function initiateSpin($gameId, $userId)
    {
        return $this->spinPrize($gameId, $userId);
    }

    /**
     * @deprecated Use spinPrize instead
     */
    public function revealPrize($gameId, $userId, $spinId)
    {
        // No-op since spinPrize handles everything
        return ['status' => false, 'message' => __('game.error.deprecated_method')];
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

    /**
     * Spin for user - select random user for a specific gift and award immediately
     */
    public function spinUserPrize($gameId, $giftId)
    {
        $game = EventGame::find($gameId);
        if (!$game) {
            return ['status' => false, 'message' => __('game.error.game_not_found')];
        }

        $gift = EventGameGift::find($giftId);
        if (!$gift || $gift->quantity <= 0) {
            return ['status' => false, 'message' => __('game.error.gift_out_of_stock')];
        }

        // Find eligible users: those checked in but NOT received any gift yet for this game
        $eventId = $game->event_id;

        // 1. Get users participating in event
        $participatingUserIds = \App\Models\EventUserHistory::where('event_id', $eventId)
            ->where('status', EventUserHistoryStatus::PARTICIPATED->value)
            ->pluck('user_id');

        if ($participatingUserIds->isEmpty()) {
            return ['status' => false, 'message' => __('game.error.no_players')];
        }

        // 2. Exclude users who already won in this game
        $winningUserIds = EventUserGift::whereIn('event_game_gift_id', $game->gifts->pluck('id'))
            ->pluck('user_id');

        $eligibleUserIds = $participatingUserIds->diff($winningUserIds);

        if ($eligibleUserIds->isEmpty()) {
            return ['status' => false, 'message' => __('game.error.no_eligible_players')];
        }

        // Randomly select one user
        $selectedUserId = $eligibleUserIds->random();
        $selectedUser = \App\Models\User::find($selectedUserId);

        if (!$selectedUser) {
            return ['status' => false, 'message' => __('game.error.cannot_select_user')];
        }

        // Prepare Wheel Items: Candidates to show on the wheel
        $wheelCandidates = $eligibleUserIds->count() > 20
            ? $eligibleUserIds->random(20)
            : $eligibleUserIds;

        // Ensure winner is in the list
        if (!$wheelCandidates->contains($selectedUserId)) {
            $wheelCandidates->push($selectedUserId);
        }

        $wheelUsers = \App\Models\User::whereIn('id', $wheelCandidates)->get()
            ->map(function ($u) {
                return [
                    'id' => (string)$u->id,
                    'option' => $u->name,
                    'image' => $u->avatar_url ? [
                        'uri' => $u->avatar_url,
                        'offsetX' => 0,
                        'offsetY' => 0,
                        'sizeMultiplier' => 0.5,
                        'landscape' => true
                    ] : null,
                    'style' => [
                        'backgroundColor' => '#' . substr(md5($u->name), 0, 6),
                        'textColor' => 'white'
                    ],
                    'user' => $u
                ];
            });

        // Save history immediately
        $history = $this->createGiftHistory($selectedUserId, $giftId);
        if (!$history['status']) {
            return ['status' => false, 'message' => $history['message']];
        }

        // Notify
        try {
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
            SendNotifications::dispatch($payload, [$selectedUserId])->delay(now()->addSeconds(3))->onQueue('notifications');
        } catch (\Throwable $e) {
            Log::error('EventGameService::spinUserPrize - Notification failed', [
                'error' => $e->getMessage(),
                'user_id' => $selectedUserId,
                'game_id' => $gameId,
            ]);
        }

        return [
            'status' => true,
            'user_id' => (string) $selectedUserId,
            'user' => $selectedUser,
            'gift' => $gift,
            'history' => $history['data'],
            'wheel_items' => $wheelUsers->values(),
        ];
    }


    /**
     * @deprecated Use spinUserPrize instead
     */
    public function revealUserPrize($gameId, $giftId, $spinId)
    {
        // No-op since spinUserPrize handles everything
        return ['status' => false, 'message' => __('game.error.deprecated_method')];
    }
}
