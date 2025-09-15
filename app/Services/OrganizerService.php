<?php

namespace App\Services;

use App\Utils\Constants\CommonStatus;
use App\Models\Organizer;
use App\Exceptions\ServiceException;
use Illuminate\Database\Eloquent\Collection;

class OrganizerService
{
    public function getActive(): Collection
    {
        return Organizer::whereNull('deleted_at')->get();
    }

    public function getActiveOptions(): array
    {
        return $this->getActive()->pluck('name', 'id')->toArray();
    }

    public function filterByName(?string $keyword = null, int $limit = 10): Collection
    {
        try {
            $query = Organizer::query()
                ->whereNull('deleted_at')
                ->where('status', CommonStatus::ACTIVE->value);

            if (!empty($keyword)) {
                $query->where('name', 'like', '%'.trim($keyword).'%');
            }

            return $query->select(['id', 'name'])
                ->limit($limit)
                ->get();
        } catch (\Exception $e) {
            return collect();
        }
    }
}
