<?php

namespace App\Services;

use App\Models\EventSchedule;
use App\Models\EventScheduleDocument;
use App\Models\EventScheduleDocumentUser;
use Illuminate\Pagination\LengthAwarePaginator;

class EventScheduleService
{

    public function getDetailSchedule($id): array
    {
        try {
            $schedule = EventSchedule::query()
                ->with([
                    'event',
                    'documents'
                ])
                ->find($id);

            if (!$schedule) {
                return [
                    'status' => false,
                    'message' => __('common.common_error.data_not_found'),
                ];
            }

            return [
                'status' => true,
                'schedule' => $schedule,
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => __('common.common_error.server_error'),
            ];
        }
    }

    public function getDetailDocument($id): array
    {
        try {
            $document = EventScheduleDocument::query()
                ->with([
                    'eventSchedule',
                    'files'
                ])
                ->find($id);

            if (!$document) {
                return [
                    'status' => false,
                    'message' => __('common.common_error.data_not_found'),
                ];
            }

            return [
                'status' => true,
                'document' => $document,
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => __('common.common_error.server_error'),
            ];
        }
    }

    public function insertEventScheduleDocumentUser($user_id, $document_id): array
    {
        try {
            $eventScheduleDocumentUser = EventScheduleDocumentUser::firstOrCreate([
                'user_id' => $user_id,
                'event_schedule_document_id' => $document_id,
            ]);

            return [
                'status' => true,
                'eventScheduleDocumentUser' => $eventScheduleDocumentUser,
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => __('common.common_error.server_error'),
            ];
        }
    }

    public function eventDocumentPaginator(array $filters = [], string $sortBy = '', int $page = 1, int $limit = 10): LengthAwarePaginator
    {
        try {
            return EventScheduleDocument::filter($filters)->sortBy($sortBy)
                ->paginate(perPage: $limit, page: $page);

        } catch (\Exception $e) {
            return new LengthAwarePaginator([], 0, $limit, $page);
        }
    }
}
