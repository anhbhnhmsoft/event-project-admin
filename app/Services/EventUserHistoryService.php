<?php

namespace App\Services;

use App\Models\EventUserHistory;
use App\Models\Event;
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

            $validStatuses = array_column(EventUserHistoryStatus::cases(), 'value');
            if (! in_array($data['status'], $validStatuses, true)) {
                return [
                    'status' => false,
                    'message' => __('event.validation.status_exists'),
                ];
            }

            $history = EventUserHistory::create([
                'event_id' => $data['event_id'],
                'user_id' => $userId,
                'event_seat_id' => $data['event_seat_id'],
                'status' => $data['status'],
            ]);

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
