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
            'Tên',
            'Email',
            'Số điện thoại',
            'Giá vé',
            'Mã vé',
            'Mã ghế',
            'Khu vực',
            'Vip',
            'Trạng thái',
            'Thời gian đăng ký',
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
