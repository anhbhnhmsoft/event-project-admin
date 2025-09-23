<?php

namespace App\Services;

use App\Models\EventUserHistory;
use App\Models\Event;
use App\Models\EventSeat;
use App\Models\User;
use App\Utils\Constants\EventSeatStatus;
use App\Utils\Constants\EventUserHistoryStatus;

class EventUserHistoryService
{
    public function createEventHistory(array $data, int $userId, int $organizerId): array
    {
        try {
            $event = Event::find($data['event_id']);
            if (! $event) {
                return [
                    'status' => false,
                    'message' => __('common.common_error.data_not_found'),
                ];
            }

            if ($event->organizer_id !== $organizerId) {
                return [
                    'status' => false,
                    'message' => __('event.error.Event_not_to_organizer'),
                ];
            }

            $history = EventUserHistory::query()->firstOrCreate(
                [
                    'event_id' => $data['event_id'],
                    'user_id' => $userId,
                ],
                [
                    'status' => EventUserHistoryStatus::SEENED->value,
                ]
            );

            if ((int)$data['status'] === EventUserHistoryStatus::SEENED->value) {
                return [
                    'status' => true,
                    'message' => $history->wasRecentlyCreated
                        ? __('common.common_success.add_success')
                        : __('common.common_success.get_success'),
                    'data' => $history,
                ];
            }

            if ((int)$history->status === EventUserHistoryStatus::BOOKED->value) {
                return [
                    'status' => false,
                    'message' => __('event.validation.already_booked'),
                ];
            }

            $user = User::find($userId);
            $canChooseSeat = $user->activeMemberships()->get()->first(fn($m) => $m->allowChooseSeat()) !== null;

            if ($canChooseSeat) {
                if (empty($data['event_seat_id'])) {
                    return [
                        'status' => false,
                        'message' => __('event.validation.event_seat_id_required'),
                    ];
                }
                $seat = EventSeat::with('area')->find($data['event_seat_id']);
                if (! $seat) {
                    return [
                        'status' => false,
                        'message' => __('event.validation.event_seat_id_exists'),
                    ];
                }
                if (($seat->area?->event_id) !== $event->id) {
                    return [
                        'status' => false,
                        'message' => __('event.validation.seat_not_in_event'),
                    ];
                }
                if ($seat->status === EventSeatStatus::BOOKED->value || $seat->user_id) {
                    return [
                        'status' => false,
                        'message' => __('event.validation.seat_taken'),
                    ];
                }
            }
            else {
                if (!empty($data['event_seat_id'])) {
                    return [
                        'status' => false,
                        'message' => __('event.validation.seat_permission_denied'),
                    ];
                }
                $seat = EventSeat::whereHas('area', function ($q) use ($event) {
                        $q->where('event_id', $event->id);
                    })
                    ->where('status', EventSeatStatus::AVAILABLE->value)
                    ->whereNull('user_id')
                    ->orderBy('id')
                    ->first();
                if (! $seat) {
                    return [
                        'status' => false,
                        'message' => __('event.validation.no_available_seat'),
                    ];
                }
                $data['event_seat_id'] = $seat->id;
            }

            $history->event_seat_id = $data['event_seat_id'];
            $history->status = EventUserHistoryStatus::BOOKED->value;
            $history->save();

            if (! empty($data['event_seat_id'])) {
                EventSeat::where('id', $data['event_seat_id'])->update([
                    'status' => EventSeatStatus::BOOKED->value,
                    'user_id' => $userId,
                ]);
            }

            return [
                'status' => true,
                'message' => __('common.common_success.add_success'),
                'data' => $history,
            ];

        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => __('common.common_error.server_error'),
            ];
        }
    }
    public function getEventHistory(int $eventId, int $userId, int $organizerId, int $page, int $limit): array
    {
        try {
            $event = Event::find($eventId);

            if (!$event) {
                return [
                    'status' => false,
                    'message' => __('common.common_error.data_not_found'),
                ];
            }

            if ($event->organizer_id !== $organizerId) {
                return [
                    'status' => false,
                    'message' => __('event.error.Event_not_to_organizer'),
                ];
            }
            $histories = EventUserHistory::where('event_id', $eventId)
                ->where('user_id', $userId)
                ->orderByDesc('created_at')
                ->paginate(perPage: $limit, page: $page);

            if ($histories->isEmpty()) {
                return [
                    'status' => false,
                    'message' => __('common.common_error.data_not_found'),
                ];
            }

            return [
                'status' => true,
                'message' => __('common.common_success.get_success'),
                'data' => $histories,
            ];

        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' =>  __('common.common_error.server_error'),
            ];
        }
    }
}
