<?php

namespace App\Filament\Resources\Events\Pages;

use App\Filament\Resources\Events\EventResource;
use App\Filament\Traits\CheckPlanBeforeAccess;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;

class SeatsEvent extends Page
{

    use InteractsWithRecord;
    protected static string $resource = EventResource::class;
    use CheckPlanBeforeAccess;

    protected string $view = 'filament.pages.seats-event';

    // Đã dịch: 'Quản lý Khu vực & Ghế ngồi'
    // protected static ?string $title = __('event.pages.seats_title');

    // Đã dịch: 'Sơ đồ chỗ ngồi'
    // protected static ?string $navigationLabel = __('event.pages.seats_nav_label');

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
        $this->ensurePlanAccessible();

    }
}
