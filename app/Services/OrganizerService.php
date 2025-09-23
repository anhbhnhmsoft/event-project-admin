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

    public function filter(array $filters = [])
    {
        $query = Organizer::query();
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['keyword'])) {
            $keyword = trim($filters['keyword']);
            $query->where('name', 'like', '%'.$keyword.'%');
        }
        return $query;
    }

    public function getOptions(array $filters = [], int $limit = 10): array
    {
        try {
            $query = $this->filter($filters);
            return $query->limit($limit)->select(['id', 'name'])->get()->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }

    public function getOrganizerDetail($id): array
    {
        try {
            $organizer = Organizer::query()
                ->with([
                    'users',
                ])
                ->find($id);

            if (!$organizer) {
                return [
                    'status' => false,
                    'message' => __('common.common_error.data_not_found'),
                ];
            }

            return [
                'status' => true,
                'organizer' => $organizer,
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => __('common.common_error.server_error'),
            ];
        }
    }
}
