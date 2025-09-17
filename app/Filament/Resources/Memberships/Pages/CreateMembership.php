<?php

namespace App\Filament\Resources\Memberships\Pages;

use Filament\Actions\Action;
use App\Filament\Resources\Memberships\MembershipResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMembership extends CreateRecord
{
    protected static string $resource = MembershipResource::class;

    protected static ?string $title = 'Tạo gói thành viên';

    public function getBreadcrumbs(): array
    {
        return [
            url()->previous() => 'Gói thành viên',
            '' => 'Tạo gói thành viên',
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
