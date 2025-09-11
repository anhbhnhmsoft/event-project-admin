<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected static ?string $title = 'Danh sách người dùng';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
            ->label('Tạo người dùng mới'),
        ];
    }

    public function getBreadcrumbs(): array
    {
        return [
            url()->previous() => 'Người dùng',
            '' => 'Danh sách',
        ];
    }
}
