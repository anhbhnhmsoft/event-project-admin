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
    
    public function getTitle(): string
    {
        return __('admin.events.pages.seats_title');
    }
    
    public function getBreadcrumbs(): array
    {
        return [
            route('filament.admin.resources.events.index') => __('admin.events.model_label'),
            '' => __('admin.events.pages.seats_title'),
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
