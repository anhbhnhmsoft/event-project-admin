<?php

namespace App\Filament\Pages;

use App\Exports\EventCheckinExport;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Dashboard as PagesDashboard;
use Maatwebsite\Excel\Facades\Excel;

class Dashboard extends PagesDashboard
{
    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\EventSelectWidget::class,
        ];
    }

    public  function getWidgets(): array
    {
        return [
            \App\Filament\Widgets\EventCheckinChart::class,
            \App\Filament\Widgets\EventStatsOverview::class,
        ];
    }

    public function getColumns(): int | array
    {
        return [
            'default' => 1,
            'md' => 2,
            'xl' => 3,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export')
                ->label(__('common.resource.dashboard.export_excel'))
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->requiresConfirmation()
                ->action(function () {
                    $eventId = session('event_id');
                    if (!$eventId) {
                        Notification::make()
                            ->title(__('common.resource.dashboard.choose_event'))
                            ->danger()
                            ->send();
                        return;
                    }
                    $export = new EventCheckinExport($eventId);
                    $fileName = "checkin-event-{$eventId}.xlsx";

                    return Excel::download($export, $fileName);;
                }),
        ];
    }
}
