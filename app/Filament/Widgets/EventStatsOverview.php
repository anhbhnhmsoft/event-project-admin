<?php

namespace App\Filament\Widgets;

use App\Services\DashboardService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Log;

class EventStatsOverview extends BaseWidget
{
    public ?string $event_id = null;
    public $start_date = null;
    public $end_date = null;
    public int $chart_type = 1;

    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = [
        'default' => 1,
        'md' => 1,
    ];

    public function mount(): void
    {
        $this->event_id = session('event_id');
        $this->start_date = session('start_date');
        $this->end_date = session('end_date');
        $this->chart_type = session('chart_type', 1) ?? 1;
    }

    #[On('eventFilterUpdated')]
    public function updateFilters($filterData): void
    {
        $this->event_id = $filterData['event_id'] ?? null;
        $this->start_date = $filterData['start_date'] ?? null;
        $this->end_date = $filterData['end_date'] ?? null;
        $this->chart_type = $filterData['chart_type'] ?? 1;
    }

    protected function getStats(): array
    {
        if (!$this->event_id) {
            return [
                Stat::make(
                    __('common.resource.dashboard.stats.no_event_title'),
                    __('common.resource.dashboard.stats.no_event_message')
                )
                    ->descriptionIcon('heroicon-o-exclamation-triangle')
                    ->color('warning'),
            ];
        }

        try {
            /** @var \App\Services\DashboardService $dashboardService */
            $dashboardService = app(DashboardService::class);
            $stats = $dashboardService->getEventStats($this->event_id);

            return [
                Stat::make(
                    __('common.resource.dashboard.stats.total_registered_title'),
                    $stats['totalRegistered']
                )
                    ->description(__('common.resource.dashboard.stats.total_registered_description')),

                Stat::make(
                    __('common.resource.dashboard.stats.total_checkin_title'),
                    $stats['totalCheckin']
                )
                    ->description(__('common.resource.dashboard.stats.total_checkin_description'))
                    ->color('success'),

                Stat::make(
                    __('common.resource.dashboard.stats.attendance_rate_title'),
                    $stats['attendanceRate'] . '%'
                )
                    ->description(__('common.resource.dashboard.stats.attendance_rate_description'))
                    ->color($stats['attendanceRate'] > 70 ? 'success' : 'warning'),
            ];
        } catch (\Exception $e) {
            Log::error('Error loading event stats:', [
                'event_id' => $this->event_id,
                'error' => $e->getMessage(),
            ]);

            return [
                Stat::make(
                    __('common.resource.dashboard.stats.error_title'),
                    __('common.resource.dashboard.stats.error_message')
                )
                    ->description($e->getMessage())
                    ->descriptionIcon('heroicon-o-x-circle')
                    ->color('danger'),
            ];
        }
    }
}
