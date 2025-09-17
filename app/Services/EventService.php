<?php

namespace App\Services;

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

}
