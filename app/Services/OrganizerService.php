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

    public function filterByName(?string $keyword = null, int $limit = 10): array
    {
        try {
            $query = Organizer::query()
                ->whereNull('deleted_at')
                ->where('status', CommonStatus::ACTIVE->value);

            if (!empty($keyword)) {
                $query->where('name', 'like', '%'.trim($keyword).'%');
            }
            $result = $query->select(['id', 'name'])
            ->limit($limit)
            ->get();
            return [
                'status' => true,
                'message' => __('organizer.success.filter_success'),
                'data' => $result,
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => __('organizer.error.filter_failed'),
            ];
        }
    }
}
