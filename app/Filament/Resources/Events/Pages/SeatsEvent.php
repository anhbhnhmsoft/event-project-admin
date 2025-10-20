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
    
    protected static ?string $title = 'Quản lý Khu vực & Ghế ngồi';
    
    public function getBreadcrumbs(): array
    {
        return [
            route('filament.admin.resources.events.index') => __('event.general.event_title'),
            '' => __('event.pages.seats_title'),
        ];
    }
    
    public function getHeading(): string
    {
        return ' ';
    }

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
        $this->ensurePlanAccessible();

    }

}
