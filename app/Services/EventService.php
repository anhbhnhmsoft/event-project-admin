<?php

namespace App\Services;

use App\Exceptions\ServiceException;
use App\Models\Event;
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

    public function getEventDetail($id): array
    {
        try {
            $event = Event::query()
                ->with([
                    'organizer:id,name,image,description',
                    'participants.user:id,name',
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
}
