<?php

namespace App\Filament\Widgets;

use App\Services\DashboardService;
use App\Utils\Constants\UnitDurationType;
use Filament\Widgets\ChartWidget;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Log;

class EventCheckinChart extends ChartWidget
{
    public ?string $event_id = null;
    public $start_date = null;
    public $end_date = null;
    public int $chart_type = 1;

    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = [
        'default' => 1,
        'md' => 2,
    ];

    public function mount(): void
    {
        $this->event_id = session('event_id');
        $this->start_date = session('start_date');
        $this->end_date = session('end_date');
        $this->chart_type = session('chart_type', UnitDurationType::HOUR->value) ?? UnitDurationType::HOUR->value;
    }

    #[On('eventFilterUpdated')]
    public function updateFilters($filterData): void
    {
        $this->event_id = $filterData['event_id'] ?? null;
        $this->start_date = $filterData['start_date'] ?? null;
        $this->end_date = $filterData['end_date'] ?? null;
        $this->chart_type = $filterData['chart_type'] ?? UnitDurationType::HOUR->value;
        $this->updateChartData();
    }

    protected function getData(): array
    {
        if (!$this->event_id) {
            return [
                'datasets' => [],
                'labels' => [__('common.resource.dashboard.chart.placeholder_no_event')],
            ];
        }

        try {
            /** @var \App\Services\DashboardService $dashboardService */
            $dashboardService = app(DashboardService::class);
            $data = $dashboardService->getCheckinChartData(
                $this->event_id,
                $this->start_date,
                $this->end_date,
                (int) $this->chart_type
            );

            return [
                'datasets' => [
                    [
                        'label' => __('common.resource.dashboard.chart.label_checkin'),
                        'data' => $data['checkinData'] ?? [],
                        'backgroundColor' => '#4ade80',
                        'borderColor' => '#4ade80',
                    ],
                    [
                        'label' => __('common.resource.dashboard.chart.label_registration'),
                        'data' => $data['registrationData'] ?? [],
                        'backgroundColor' => '#3b82f6',
                        'borderColor' => '#3b82f6',
                    ],
                ],
                'labels' => $data['labels'] ?? [],
            ];
        } catch (\Exception $e) {
            Log::error('Error loading chart data:', [
                'event_id' => $this->event_id,
                'error' => $e->getMessage(),
            ]);

            return [
                'datasets' => [],
                'labels' => ['Lỗi: ' . $e->getMessage()],
            ];
        }
    }

    protected function getType(): string
    {
        return 'bar';
    }

    public function getHeading(): ?string
    {
        $typeKey = $this->chart_type == UnitDurationType::HOUR->value
            ? 'common.resource.dashboard.chart.type_hour'
            : 'common.resource.dashboard.chart.type_day';

        $type = __($typeKey);
        return __('common.resource.dashboard.chart.heading') . ' ' . $type;
    }
}
