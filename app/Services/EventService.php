<?php

namespace App\Services;

use App\Jobs\SendEventEmail;
use App\Jobs\SendNotifications;
use App\Models\Event;
use App\Models\EventArea;
use App\Models\EventSeat;
use App\Models\EventUserHistory;
use App\Models\Organizer;
use App\Models\User;
use App\Utils\Constants\EventSeatStatus;
use App\Utils\Constants\EventStatus;
use App\Utils\Constants\UserNotificationType;
use App\Utils\DTO\NotificationPayload;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
        } else {
            return [];
        }

        return $query->pluck('name', 'id');
    }

    public function getEventArea($eventId)
    {
        return EventArea::query()
            ->where('event_id', $eventId)
            ->withCount(['seats as seat_available_count' => function ($q) {
                $q->where('status', EventSeatStatus::AVAILABLE->value);
            }])
            ->get();
    }

    public function getAreaById($areaId, $eventId)
    {
        try {
            $area = EventArea::query()
                ->where('event_id', $eventId)
                ->where('id', $areaId)
                ->first();
            if (!$area) {
                return [
                    'status' => false,
                    'message' => __('common.common_error.data_not_found'),
                ];
            }

            return [
                'status' => true,
                'data' => $area,
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => __('common.common_error.server_error'),
            ];
        }
    }

    public function getSeatsByAreaId($areaId)
    {
        return EventSeat::query()
            ->where('event_area_id', $areaId)
            ->orderByRaw('CAST(seat_code AS UNSIGNED) ASC')
            ->get();
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
        DB::beginTransaction();
        try {
            $now = now();

            // Cập nhật trạng thái sự kiện sắp diễn ra
            Event::query()
                ->where('status', EventStatus::UPCOMING->value)
                // Điều kiện 1: Thời gian bắt đầu phải nhỏ hơn hoặc bằng hiện tại (Đã bắt đầu)
                ->whereRaw('CONCAT(DATE(day_represent), " ", TIME(start_time)) <= ?', [$now])

                // Điều kiện 2: Thời gian kết thúc phải lớn hơn hoặc bằng hiện tại (Chưa kết thúc)
                ->whereRaw('CONCAT(DATE(day_represent), " ", TIME(end_time)) >= ?', [$now])
                ->update(['status' => EventStatus::ACTIVE->value]);

            // Cập nhật trạng thái sự kiện đang diễn ra
            Event::query()
                ->where('status', EventStatus::ACTIVE->value)
                // Điều kiện: Thời gian kết thúc phải nhỏ hơn hoặc bằng hiện tại (Đã kết thúc)
                ->whereRaw('CONCAT(DATE(day_represent), " ", TIME(end_time)) <= ?', [$now])
                ->update(['status' => EventStatus::CLOSED->value]);

            DB::commit();
            return [
                'status' => true,
                'message' => __('common.common_success.update_success')
            ];
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Error in checkTimeEvent: " . $e->getMessage());
            return [
                'status' => false,
                'message' => __('common.common_error.server_error'),
            ];
        }
    }

    /**
     * Chạy vào 6h sáng mỗi ngày
     * @return array
     */
    public function notificationTimeEvent(): array
    {
        try {
            $now = now();
            $today = $now->copy()->format('Y-m-d');
            $eventsUpcoming = Event::query()
                ->where('status', EventStatus::UPCOMING->value)
                ->whereDate('day_represent', $today)
                ->get();
            foreach ($eventsUpcoming as $event) {
                // ---  Lấy danh sách user đã đặt vé hoặc xem vé ---
                $userIds = EventUserHistory::query()
                    ->where('event_id', $event->id)
                    ->pluck('user_id')
                    ->toArray();

                if (empty($userIds)) {
                    continue;
                }

                // ---  Tạo nội dung notification ---
                $payload = new NotificationPayload(
                    title: __('event.success.notification_title_event_start', ['name' => $event->title]),
                    description: __('event.success.notification_desc_event_start'),
                    data: [],
                    notificationType: UserNotificationType::EVENT_REMINDER
                );

                // ---  Gửi push notification ---
                SendNotifications::dispatch($payload, $userIds)->onQueue('notifications');

                // ---  Lấy email của user ---
                $emails = User::query()
                    ->whereIn('id', $userIds)
                    ->pluck('email')
                    ->toArray();

                // ---  Gửi email ---
                if (!empty($emails)) {
                    SendEventEmail::dispatch(
                        $emails,
                        __('event.mail.subject_event_start', ['name' => $event->title]),
                        [
                            'event_name' => $event->title,
                            'start_time' => $event->start_time,
                            'short_description' => $event->short_description,
                            'address' => $event->address,
                            'organizer_name' => $event->organizer->name ?? __('organizer.label.name'),
                            'latitude' => $event->latitude,
                            'longitude' => $event->longitude,
                            'map_link'  => "https://www.google.com/maps?q={$event->latitude},{$event->longitude}",
                            'event_id'  => $event->id,
                        ]
                    )->onQueue('emails');
                }
            }

            return [
                'status' => true,
                'message' => __('common.common_success.update_success')
            ];
        } catch (\Throwable $th) {
            return [
                'status' => false,
                'message' => __('common.common_error.server_error'),
            ];
        }
    }
}
