<?php

namespace App\Services;

use App\Models\EventGame;
use App\Models\EventGameGift;
use App\Models\EventUserGift;
use App\Utils\Constants\ConfigGameEvent;
use App\Utils\Constants\ConfigMembership;
use App\Utils\Constants\EventUserHistoryStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EventGameService
{
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
            $event = $game->event;

            $query = $event->usersHasTicket()
                ->whereHas('eventUserHistories', function ($q) {
                    $q->where('status', EventUserHistoryStatus::PARTICIPATED->value);
                });

            if (
                isset($game->config_game[ConfigGameEvent::REQUIRE_MEMBERSHIP->value])
                && $game->config_game[ConfigGameEvent::REQUIRE_MEMBERSHIP->value]
            ) {
                $query->whereHas('activeMemberships', function ($q) {
                    $q->whereJsonContains('config->' . ConfigMembership::ALLOW_PLAYGAME->value, true);
                });
            }

            $users = $query->paginate($perPage);

            return [
                'status' => true,
                'data'   => $users,
            ];
        } catch (\Exception $e) {
            return [
                'status'  => false,
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
                    'message' => 'Không tìm thấy quà.',
                ];
            }

            if ($gift->quantity <= 0) {
                return [
                    'status'  => false,
                    'message' => 'Quà đã hết.',
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
                'message' => 'Lưu lịch sử quà tặng thành công.',
                'data'    => $history,
            ];
        } catch (\Throwable $e) {
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

        return $game->event->organizer_id === $user->organizer_id;
    }
}
