<?php

namespace App\Services;

use App\Models\EventUserHistory;
use App\Utils\Constants\EventUserHistoryStatus;
use App\Utils\Constants\UnitDurationType;
use Carbon\Carbon;

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

    public function getCheckinChartData(string $eventId, $startDate, $endDate, $chartType): array
    {
        $start = null;
        $end = null;

        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        if ($chartType == UnitDurationType::DAY->value) {
            $selectRaw = "DATE(created_at) as period, COUNT(*) as total";
            $groupBy = "DATE(created_at)";
            $orderBy = "DATE(created_at)";
            $labelFormat = fn($date) => Carbon::parse($date)->format('d/m');
            $periodRange = null;
        } else {
            $selectRaw = "HOUR(created_at) as period, COUNT(*) as total";
            $groupBy = "HOUR(created_at)";
            $orderBy = "HOUR(created_at)";
            $labelFormat = fn($i) => $i . 'h';
            $periodRange = range(0, 23);
        }

        $checkinQuery = EventUserHistory::where('event_id', $eventId)
            ->where('status', EventUserHistoryStatus::PARTICIPATED);

        $registrationQuery = EventUserHistory::where('event_id', $eventId);

        if ($startDate && $endDate) {
            $checkinQuery->whereBetween('created_at', [$start, $end]);
            $registrationQuery->whereBetween('created_at', [$start, $end]);
            $checkinQuery->selectRaw($selectRaw)->groupByRaw($groupBy)->orderByRaw($orderBy);
            $registrationQuery->selectRaw($selectRaw)->groupByRaw($groupBy)->orderByRaw($orderBy);
        } else if ($chartType == UnitDurationType::HOUR->value) {
            $checkinQuery->selectRaw('COUNT(*) as total, HOUR(created_at) as period')->groupByRaw('HOUR(created_at)');
            $registrationQuery->selectRaw('COUNT(*) as total, HOUR(created_at) as period')->groupByRaw('HOUR(created_at)');
        } else if ($chartType == UnitDurationType::DAY->value) {
            $checkinQuery->selectRaw('COUNT(*) as total, DATE(created_at) as period')->groupByRaw('DATE(created_at)');
            $registrationQuery->selectRaw('COUNT(*) as total, DATE(created_at) as period')->groupByRaw('DATE(created_at)');
        }

        $checkinDataRaw = $checkinQuery->pluck('total', 'period')->toArray();
        $registrationDataRaw = $registrationQuery->pluck('total', 'period')->toArray();

        $labels = [];
        $checkinData = [];
        $registrationData = [];

        if ($start && $end && $chartType == UnitDurationType::HOUR) {
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
