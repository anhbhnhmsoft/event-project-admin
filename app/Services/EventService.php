<?php

namespace App\Services;

use App\Models\Event;
use App\Utils\Helper;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EventService
{
    public function filter(array $filters = [])
    {
        $query = Event::query();

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['start_time'])) {
            $start = \Carbon\Carbon::parse($filters['start_time'])->startOfDay();
            $query->whereDate('start_time', '>=', $start);
        }

        if (!empty($filters['end_time'])) {
            $end = \Carbon\Carbon::parse($filters['end_time'])->endOfDay();
            $query->whereDate('end_time', '<=', $end);
        }

        if (!empty($filters['province_code'])) {
            $query->where('province_code', $filters['province_code']);
        }

        if (!empty($filters['district_code'])) {
            $query->where('district_code', $filters['district_code']);
        }

        if (!empty($filters['ward_code'])) {
            $query->where('ward_code', $filters['ward_code']);
        }

        if (!empty($filters['organizer_id'])) {
            $query->where('organizer_id', $filters['organizer_id']);
        }

        if (!empty($filters['keyword'])) {
            $keyword = trim($filters['keyword']);
            $query->where('name', 'like', '%' . $keyword . '%')->orWhere('address', 'like', '%' . $keyword . '%');;
        }

        return $query;
    }

    public function getEvents(array $filters = [], int $page = 1, int $limit = 10): array
    {
        try {
            $query = $this->filter($filters);
            $sortBy = $filters['sortBy'] ?? 'created_at';
            $lat = isset($filters['lat']) ? (float) $filters['lat'] : null;
            $lng = isset($filters['lng']) ? (float) $filters['lng'] : null;

            $hasDistance = $this->distanceSelect($query, $lat, $lng);
            $query = $this->sortBy($query, $sortBy, 'desc', $hasDistance);

            $total = $query->count();
            $lastPage = (int) ceil($total / $limit);
            $paged = $query->paginate($limit)->values();
            $collection = $paged->map(function ($event) {
                $data = $event->toArray();
                $data['start_time'] = date('Y-m-d H:i:s', strtotime($event->start_time));
                $data['end_time']   = date('Y-m-d H:i:s', strtotime($event->end_time));
                return $data;
            });
            $meta = [
                'current_page' => $page,
                'last_page' => $lastPage,
                'per_page' =>  $limit,
                'total' => $total
            ];
            return [
                'data' => $collection,
                'meta' => $meta
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

    public function sortBy(Builder $query, string $sortBy, string $direction = 'desc', bool $hasDistance = false)
    {
        switch ($sortBy) {
            case 'name':
                $query->orderBy('name', $direction);
                break;
            case 'distance':
                if ($hasDistance) {
                    $query->whereNotNull('latitude')->whereNotNull('longitude');
                    $query->orderBy('distance_km', 'asc');
                }
                break;
            default:
                $query->orderBy('created_at', $direction);
                break;
        }
        return $query;
    }

    private function distanceSelect($query, ?float $lat, ?float $lng): bool
    {
        if ($lat && $lng) {
            $query->selectRaw(
                '(6371 * acos( cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)) )) as distance_km',
                [$lat, $lng, $lat]
            );
            return true;
        }
        return false;
    }
}
