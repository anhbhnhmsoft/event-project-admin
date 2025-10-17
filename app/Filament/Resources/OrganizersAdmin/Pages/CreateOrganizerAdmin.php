<?php

namespace App\Filament\Resources\OrganizersAdmin\Pages;

use App\Filament\Resources\OrganizersAdmin\OrganizerAdminResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreateOrganizerAdmin extends CreateRecord
{
    protected static string $resource = OrganizerAdminResource::class;

    protected static ?string $title = 'Tạo nhà tổ chức';

    public function getBreadcrumbs(): array
    {
        return [
            url()->previous() => 'Nhà tổ chức',
            '' => 'Tạo nhà tổ chức',
        ];
    }

    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()->label('Tạo mới');
    }

    protected function getCreateAnotherFormAction(): Action
    {
        return parent::getCreateAnotherFormAction()
            ->label('Tạo và tạo thêm');
    }

    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()->label('Hủy');
    }
}


