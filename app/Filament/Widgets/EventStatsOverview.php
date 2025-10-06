<?php

namespace App\Filament\Widgets;

use App\Services\DashboardService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class EventStatsOverview extends BaseWidget
{
    public ?string $event_id = null;

    protected static bool $isLazy = true;

    protected int|string|array $columnSpan = [
        'default' => 1,
        'md' => 1,
    ];

    protected function getStats(): array
    {
        $eventId = session('event_id');
        if (!$eventId) {
            return [
                Stat::make('Chưa chọn sự kiện', 'Vui lòng chọn sự kiện để xem thống kê.')
                    ->descriptionIcon('heroicon-o-exclamation-triangle')
                    ->color('warning'),
            ];
        }

        /** @var \App\Services\DashboardService $dashboardService */
        $dashboardService = app(DashboardService::class);
        $stats = $dashboardService->getEventStats($eventId);

        return [
            Stat::make('Tổng đăng ký', $stats['totalRegistered'])
                ->description('Số người đăng ký tham gia sự kiện'),

            Stat::make('Số Check-in', $stats['totalCheckin'])
                ->description('Số người đã check-in')
                ->color('success'),

            Stat::make('Tỉ lệ tham dự', $stats['attendanceRate'] . '%')
                ->description('So với tổng số đăng ký')
                ->color($stats['attendanceRate'] > 70 ? 'success' : 'warning'),
        ];
    }
}
