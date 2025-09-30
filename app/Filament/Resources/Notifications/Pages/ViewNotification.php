<?php

namespace App\Filament\Resources\Notifications\Pages;

use App\Filament\Resources\Notifications\NotificationResource;
use Filament\Resources\Pages\ViewRecord;

class ViewNotification extends ViewRecord
{
    protected static string $resource = NotificationResource::class;
    protected static ?string $title = 'Xem thông báo';

    public function getBreadcrumbs(): array
    {
        return [
            url()->previous() => 'Thông báo',
            '' => 'Xem thông báo',
        ];
    }
}


