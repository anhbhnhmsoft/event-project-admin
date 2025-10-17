<?php

namespace App\Filament\Resources\OrganizersAdmin\Pages;

use App\Filament\Resources\OrganizersAdmin\OrganizerAdminResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListOrganizersAdmin extends ListRecords
{
    protected static string $resource = OrganizerAdminResource::class;

    protected static ?string $title = 'Danh sách nhà tổ chức';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Tạo mới'),
        ];
    }

    public function getBreadcrumbs(): array
    {
        return [
            url()->previous() => 'Nhà tổ chức',
            '' => 'Danh sách',
        ];
    }
}


