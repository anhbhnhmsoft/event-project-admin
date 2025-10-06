<?php

namespace App\Services;

use App\Exceptions\ServiceException;
use App\Models\Event;
use App\Models\Organizer;
use App\Utils\Constants\EventStatus;
use Illuminate\Pagination\LengthAwarePaginator;

class EventService
{
    public function eventPaginator(array $filters = [], string $sortBy = '', int $page = 1, int $limit = 10): LengthAwarePaginator
    {
        try {
            return Event::filter($filters)->sortBy($sortBy)
                ->paginate(perPage: $limit, page: $page);

        } catch (\Exception $e) {
            return new LengthAwarePaginator([], 0, $limit, $page);
        }
    }

    public function getAllOrganizersList()
    {
        return Organizer::query()->pluck('name', 'id');
    }

    public function getEventsListByOrganizerId(?string $organizerId)
    {
        $query = Event::query();

        if ($organizerId) {
            $query->where('organizer_id', $organizerId);
        }else {
            return [];
        }

        return $query->pluck('name', 'id');
    }

    public function getEventDetail($id): array
    {
        try {
            $event = Event::query()
                ->with([
                    'organizer:id,name,image,description',
                    'participants.user:id,name,avatar_path',
                    'schedules:id,event_id,title,sort',
                ])
                ->find($id);

            if (!$event) {
                return [
                    'status' => false,
                    'message' => __('common.common_error.data_not_found'),
                ];
            }

            return [
                'status' => true,
                'event' => $event,
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => __('common.common_error.server_error'),
            ];
        }
    }

    public function checkTimeEvent(): array
    {
        try {
            $now = now();

            Event::where('status', EventStatus::UPCOMING->value)
                ->whereRaw('CONCAT(DATE(day_represent), " ", TIME(start_time)) <= ?', [$now])
                ->update(['status' => EventStatus::ACTIVE->value]);

            Event::where('status', EventStatus::ACTIVE->value)
                ->whereRaw('CONCAT(DATE(day_represent), " ", TIME(end_time)) < ?', [$now])
                ->update(['status' => EventStatus::CLOSED->value]);

            return [
                'status' => true,
                'message' => __('common.common_success.update_success')
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => __('common.common_error.server_error'),
            ];
        }
    }
}
