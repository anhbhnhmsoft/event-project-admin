<?php

namespace App\Filament\Resources\Events\Pages;

use App\Filament\Resources\Events\EventResource;
use App\Utils\Helper;
use Carbon\Carbon;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;

class EventDetailSpeaker extends Page
{
    use InteractsWithRecord;

    protected static string $resource = EventResource::class;

    protected string $view = 'filament.pages.event-detail-speaker';

    public $schedules = [];
    public $participants = [];
    public $currentScheduleIndex = null;
    public $nextScheduleIndex = null;
    public $previousScheduleIndex = null;
    public $isToday = false;
    public $eventProgress = 0;
    public $timeRemaining = null;

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
        $this->loadEventData();
        $this->ensurePlanAccessible();
    }

    protected function loadEventData(): void
    {
        $event = $this->record;

        // Load schedules và sắp xếp theo thứ tự
        $this->schedules = $event->schedules()
            ->orderBy('sort')
            ->with('documents')
            ->get()
            ->toArray();

        // Load participants
        $this->participants = $event->users()
            ->with('user')
            ->get()
            ->map(function ($eventUser) {
                return [
                    'id' => $eventUser->user->id,
                    'name' => $eventUser->user->name,
                    'role' => $eventUser->role,
                    'role_label' => \App\Utils\Constants\EventUserRole::options()[$eventUser->role] ?? '',
                    'avatar' => $eventUser->user->avatar_path ?? null,
                ];
            })
            ->toArray();

        // Kiểm tra xem sự kiện có diễn ra hôm nay không
        $this->isToday = Carbon::parse($event->day_represent)->isToday();

        if ($this->isToday) {
            $this->calculateCurrentSchedule();
            $this->calculateEventProgress();
        }
    }

    protected function calculateCurrentSchedule(): void
    {
        $currentTime = now()->format('H:i');
        $nowMin = Helper::timeToMinutes($currentTime);

        foreach ($this->schedules as $index => $schedule) {
            $startMin = Helper::timeToMinutes($schedule['start_time']);
            $endMin = Helper::timeToMinutes($schedule['end_time']);

            // Lịch trình đang diễn ra
            if ($nowMin >= $startMin && $nowMin < $endMin) {
                $this->currentScheduleIndex = $index;
                $this->nextScheduleIndex = $index + 1 < count($this->schedules) ? $index + 1 : null;
                $this->previousScheduleIndex = $index > 0 ? $index - 1 : null;

                // Tính thời gian còn lại
                $this->timeRemaining = $endMin - $nowMin;
                break;
            }

            // Lịch trình sắp tới
            if ($nowMin < $startMin && $this->currentScheduleIndex === null) {
                $this->nextScheduleIndex = $index;
                $this->previousScheduleIndex = $index > 0 ? $index - 1 : null;
                $this->timeRemaining = $startMin - $nowMin;
                break;
            }
        }

        // Nếu đã qua tất cả lịch trình
        if ($this->currentScheduleIndex === null && $this->nextScheduleIndex === null) {
            $this->previousScheduleIndex = count($this->schedules) - 1;
        }
    }

    protected function calculateEventProgress(): void
    {
        $event = $this->record;
        $eventStartMin = Helper::timeToMinutes($event->start_time);
        $eventEndMin = Helper::timeToMinutes($event->end_time);
        $nowMin = Helper::timeToMinutes(now()->format('H:i'));

        if ($nowMin < $eventStartMin) {
            $this->eventProgress = 0;
        } elseif ($nowMin > $eventEndMin) {
            $this->eventProgress = 100;
        } else {
            $totalDuration = $eventEndMin - $eventStartMin;
            $elapsed = $nowMin - $eventStartMin;
            $this->eventProgress = round(($elapsed / $totalDuration) * 100, 2);
        }
    }

    public function refreshData(): void
    {
        $this->loadEventData();
        $this->dispatch('data-refreshed');
    }

    public function getScheduleProgress(int $index): float
    {
        if (!$this->isToday || $index !== $this->currentScheduleIndex) {
            return 0;
        }

        $schedule = $this->schedules[$index];
        $startMin = Helper::timeToMinutes($schedule['start_time']);
        $endMin = Helper::timeToMinutes($schedule['end_time']);
        $nowMin = Helper::timeToMinutes(now()->format('H:i'));

        if ($nowMin < $startMin) {
            return 0;
        } elseif ($nowMin > $endMin) {
            return 100;
        }

        $totalDuration = $endMin - $startMin;
        $elapsed = $nowMin - $startMin;
        return round(($elapsed / $totalDuration) * 100, 2);
    }

    public function formatTimeRemaining(?int $minutes): string
    {
        if ($minutes === null) {
            return '';
        }

        $hours = floor($minutes / 60);
        $mins = $minutes % 60;

        if ($hours > 0) {
            return sprintf('%dh %02dm', $hours, $mins);
        }

        return sprintf('%d phút', $mins);
    }

    public function getPollingInterval(): string
    {
        return '60s';
    }
}
