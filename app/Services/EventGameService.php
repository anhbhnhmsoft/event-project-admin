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
use App\Jobs\SendNotifications;
use App\Utils\DTO\NotificationPayload;
use App\Utils\Constants\UserNotificationType;

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
            $customRates = $game->config_game['custom_user_rates'] ?? [];
            if (empty($customRates)) {
                return [
                    'status' => false,
                    'message' => 'Chưa có người chơi được cấu hình tỉ lệ.',
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
                    'message' => 'Không có người chơi hợp lệ trong cấu hình.',
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

        return $game->event->organizer_id === $user->organizer_id;
    }

    public function spinPrize($gameId, $userId)
    {
        $game = EventGame::with('gifts')->find($gameId);

        if (!$game) {
            return ['status' => false, 'message' => 'Game không tồn tại.'];
        }

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
            return ['status' => false, 'message' => 'Không còn phần quà hợp lệ.'];
        }

        $totalRate = $gifts->sum('rate');
        $random = mt_rand(1, $totalRate);
        $cumulative = 0;

        foreach ($gifts as $gift) {
            $cumulative += $gift->rate;
            if ($random <= $cumulative) {
                $gift->decrement('quantity');

                $history = $this->createGiftHistory($userId, $gift->id);
                if (!$history['status']) {
                    return ['status' => false, 'message' => $history['message']];
                }

                try {
                    $payload = new NotificationPayload(
                        title: __('event.success.congratulartion_prize'),
                        description: __('event.success.congratulartion_desc',[$gift->name, $game->name]),
                        data: [
                            'game_id' => $game->id,
                            'gift_id' => $gift->id,
                            'history_id' => $history['data']->id,
                        ],
                        notificationType: UserNotificationType::SYSTEM_ANNOUNCEMENT,
                    );

                    SendNotifications::dispatch($payload, [$userId])->onQueue('notifications');
                } catch (\Throwable $e) {
                    Log::error('EventGameService::spinPrize - Gửi thông báo thất bại', [
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
        }

        return ['status' => false, 'message' => 'Không chọn được phần quà.'];
    }
}
