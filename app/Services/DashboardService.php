<?php

namespace App\Services;

use App\Models\EventUserHistory;
use App\Utils\Constants\EventUserHistoryStatus;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function getEventStats(string $eventId): array
    {
        $totalRegistered = EventUserHistory::where('event_id', $eventId)->count();

        $totalCheckin = EventUserHistory::where('event_id', $eventId)
            ->where('status', EventUserHistoryStatus::PARTICIPATED)
            ->count();

        $attendanceRate = $totalRegistered > 0
            ? round(($totalCheckin / $totalRegistered) * 100, 2)
            : 0;

        return [
            'totalRegistered' => $totalRegistered,
            'totalCheckin'    => $totalCheckin,
            'attendanceRate'  => $attendanceRate,
        ];
    }

    public function getCheckinChartData(string $eventId, $startDate, $endDate, string $chartType = 'hour'): array
    {
        if ($chartType === 'hour') {
            $selectRaw = "HOUR(created_at) as period, COUNT(*) as total";
            $groupBy = "HOUR(created_at)";
            $orderBy = "HOUR(created_at)";
            $labelFormat = fn($i) => $i . 'h';
            $periodRange = range(0, 23);
        } else {
            $selectRaw = "DATE(created_at) as period, COUNT(*) as total";
            $groupBy = "DATE(created_at)";
            $orderBy = "DATE(created_at)";
            $labelFormat = fn($date) => \Carbon\Carbon::parse($date)->format('d/m');
            $periodRange = null;
        }

        $checkinDataRaw = EventUserHistory::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw($selectRaw)
            ->where('event_id', $eventId)
            ->where('status', EventUserHistoryStatus::PARTICIPATED)
            ->groupByRaw($groupBy)
            ->orderByRaw($orderBy)
            ->pluck('total', 'period')
            ->toArray();

        $registrationDataRaw = EventUserHistory::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw($selectRaw)
            ->where('event_id', $eventId)
            ->groupByRaw($groupBy)
            ->orderByRaw($orderBy)
            ->pluck('total', 'period')
            ->toArray();

        $labels = [];
        $checkinData = [];
        $registrationData = [];

        if ($chartType === 'hour') {
            foreach ($periodRange as $i) {
                $labels[] = $labelFormat($i);
                $checkinData[] = $checkinDataRaw[$i] ?? 0;
                $registrationData[] = $registrationDataRaw[$i] ?? 0;
            }
        } else {
            $allPeriods = array_keys($checkinDataRaw + $registrationDataRaw);
            sort($allPeriods);

            foreach ($allPeriods as $period) {
                $labels[] = $labelFormat($period);
                $checkinData[] = $checkinDataRaw[$period] ?? 0;
                $registrationData[] = $registrationDataRaw[$period] ?? 0;
            }
        }

        return [
            'labels' => $labels,
            'checkinData' => $checkinData,
            'registrationData' => $registrationData,
        ];
    }
}
