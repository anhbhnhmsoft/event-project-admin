<?php

namespace App\Exports;

use App\Models\EventUserHistory;
use App\Utils\Constants\EventUserHistoryStatus;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class EventCheckinExport implements FromCollection, WithHeadings, WithMapping
{
    protected string $eventId;

    public function __construct(string $eventId)
    {
        $this->eventId = $eventId;
    }

    /**
     * Lấy dữ liệu cho export
     */
    public function collection()
    {
        Log::debug(EventUserHistory::with([
            'user:id,name,email,phone',
            'seat:id,seat_code,event_area_id',
            'seat.area:id,name,vip,price',
        ])
            ->where('event_id', $this->eventId)
            ->orderBy('created_at')
            ->get());
        return EventUserHistory::with([
            'user:id,name,email,phone',
            'seat:id,seat_code,event_area_id',
            'seat.area:id,name,vip,price',
        ])
            ->where('event_id', $this->eventId)
            ->orderBy('created_at')
            ->get();
    }

    public function headings(): array
    {
        return [
            __('constants.event_export.user_name'),
            __('constants.event_export.email'),
            __('constants.event_export.phone'),
            __('constants.event_export.ticket_price'),
            __('constants.event_export.ticket_code'),
            __('constants.event_export.seat_code'),
            __('constants.event_export.area_name'),
            __('constants.event_export.vip'),
            __('constants.event_export.status'),
            __('constants.event_export.created_at'),
        ];
    }

    public function map($history): array
    {
        return [
            data_get($history, 'user.name', '—'),
            data_get($history, 'user.email', '—'),
            data_get($history, 'user.phone', '—'),
            data_get($history, 'seat.area.price')
                ? number_format(data_get($history, 'seat.area.price'), 0, ',', '.') . '₫'
                : 'Miễn phí',
            data_get($history, 'ticket_code', '—'),
            data_get($history, 'seat.seat_code', '—'),
            data_get($history, 'seat.area.name', '—'),
            data_get($history, 'seat.area.vip') ? 'Có' : 'Không',
            EventUserHistoryStatus::getLabel($history->status),
            Carbon::parse($history->created_at)->format('d/m/Y H:i'),
        ];
    }
}
