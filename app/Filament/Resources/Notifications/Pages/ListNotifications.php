<?php

namespace App\Filament\Resources\Notifications\Pages;

use App\Filament\Resources\Notifications\NotificationResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\CreateAction;


class ListNotifications extends ListRecords
{
    protected static string $resource = NotificationResource::class;

    protected static ?string $title = 'Danh sách thông báo';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Tạo mới'),
        ];
    }

    public function getBreadcrumbs(): array
    {
        return [
            url()->previous() => 'Thông báo',
            '' => 'Danh sách thông báo',
        ];
    }

}


