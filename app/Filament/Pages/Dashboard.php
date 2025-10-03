<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as PagesDashboard;

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

}
