<?php

namespace App\Filament\Resources\Notifications\Pages;

use App\Filament\Resources\Notifications\NotificationResource;
use Filament\Resources\Pages\ViewRecord;

class ViewNotification extends ViewRecord
{
    protected static string $resource = NotificationResource::class;
    public function getTitle(): string
    {
        return __('admin.notifications.pages.view_title');
    }

    public function getBreadcrumbs(): array
    {
        return [
            url()->previous() => 'Thông báo',
            '' => 'Xem thông báo',
        ];
    }
}


