<?php

namespace App\Services;

use App\Models\Event;
use App\Utils\Helper;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EventService
{
    public function filter(array $filters = [])
    {
        $query = Event::query();

        if (!empty($filter['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filter['province_code'])) {
            $query->where('province_code', $filters['province_code']);
        }

        if (!empty($filter['district_code'])) {
            $query->where('district_code', $filters['district_code']);
        }

        if (!empty($filter['ward_code'])) {
            $query->where('ward_code', $filters['ward_code']);
        }

        if (!empty($filters['organizer_id'])) {
            $query->where('organizer_id', $filters['organizer_id']);
        }

        if (!empty($filters['keyword'])) {
            $keyword = trim($filters['keyword']);
            $query->where('name', 'like', '%' . $keyword . '%');
        }

        return $query;
    }

    public function getEventById(int $id): ?Event
    {
        return Event::find($id);
    }

    public function getEvents(array $filters = [], int $page = 1, int $limit = 10): array
    {
        try {
            $query = $this->filter($filters);
            $events = $query->get();

            if (!empty($filters['user_lat']) && !empty($filters['user_lng'])) {
                $userLat = (float) $filters['user_lat'];
                $userLng = (float) $filters['user_lng'];

                $events->transform(function ($event) use ($userLat, $userLng) {
                    $event->distance = Helper::calculateDistance(
                        $userLat,
                        $userLng,
                        (float) $event->latitude,
                        (float) $event->longitude
                    );
                    return $event;
                });

                $events = $events->sortBy('distance')->values();
            }

            $total    = $events->count();
            $lastPage = (int) ceil($total / $limit);
            $paged = $events->forPage($page, $limit)->values();
            $collection = $paged->map(function ($event) {
                return [
                    'id' => $event->id,
                    'name' => $event->name,
                    'image_represent_path' => $event->image_represent_path,
                    'address' => $event->address,
                    'day_represent' => date('d/m/Y', strtotime($event->day_represent)),
                    'distance' => $event->distance ?? null,
                    'short_description' => $event->short_description,
                    'start_time' => $event->start_time,
                    'end_time' => $event->end_time
                ];
            });
            return [
                'data' => $collection,
                'meta' => [
                    'current_page' => $page,
                    'last_page' => $lastPage,
                    'per_page' =>  $limit,
                    'total' => $total
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Error getEvents: ' . $e->getMessage());
            return [
                'message' => __('event.error.list_failed'),
                'data' => collect([]),
                'meta' => [
                    'current_page' => $page,
                    'last_page' => 0,
                    'per_page' => $limit,
                    'total' => 0,
                ]
            ];
        }
    }
}
