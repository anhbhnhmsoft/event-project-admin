<?php

namespace App\Filament\Widgets;

use App\Services\DashboardService;
use Filament\Widgets\ChartWidget;

class EventCheckinChart extends ChartWidget
{
    public ?string $event_id = null;

    protected static bool $isLazy = true;

    protected int|string|array $columnSpan = [
        'default' => 1,
        'md' => 2,
    ];

    protected function getData(): array
    {
        $eventId = session('event_id');
        $startDate = session('start_date');
        $endDate = session('end_date');
        $chartType = session('chart_type', 'hour');

        if (!$eventId) {
            return [
                'labels' => ['Vui lòng chọn sự kiện để thống kê dữ liệu']
            ];
        }
        /** @var \App\Services\DashboardService $dashboardService */
        $dashboardService = app(DashboardService::class);
        $data = $dashboardService->getCheckinChartData($eventId, $startDate, $endDate, $chartType);

        return [
            'datasets' => [
                [
                    'label' => 'Số check-in',
                    'data' => $data['checkinData'],
                    'backgroundColor' => '#4ade80',
                    'borderColor' => '#4ade80',
                ],
                [
                    'label' => 'Số đăng ký',
                    'data' => $data['registrationData'],
                    'backgroundColor' => '#3b82f6',
                    'borderColor' => '#3b82f6',
                ],
            ],
            'labels' => $data['labels'],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    public function getHeading(): ?string
    {
        $chartType = session('chart_type', 'hour');
        $type = $chartType === 'hour' ? 'Theo Giờ' : 'Theo Ngày';
        return 'Thống kê check-in & đăng ký ' . $type;
    }
}
