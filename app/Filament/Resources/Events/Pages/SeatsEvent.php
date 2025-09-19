<?php

namespace App\Filament\Resources\Events\Pages;

use App\Filament\Resources\Events\EventResource;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;

class SeatsEvent extends Page
{

    use InteractsWithRecord;
    protected static string $resource = EventResource::class;

    protected string $view = 'filament.pages.seats-event';

    protected static ?string $title = 'Quản lý Khu vực & Ghế ngồi';

    protected static ?string $navigationLabel = 'Sơ đồ chỗ ngồi';

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }
}
