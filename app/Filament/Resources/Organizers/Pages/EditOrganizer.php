<?php

namespace App\Filament\Resources\Organizers\Pages;

use App\Filament\Resources\Organizers\OrganizerResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions\Action;

class EditOrganizer extends EditRecord
{
    protected static string $resource = OrganizerResource::class;

    protected static ?string $title = 'Sửa nhà tổ chức';

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
            ->label('Xóa'),
        ];
    }
    public function getBreadcrumbs(): array
    {
        return [
            url()->previous() => 'Nhà tổ chức',
            '' => 'Sửa nhà tổ chức',
        ];
    }

    protected function getSaveFormAction(): Action
    {
        return parent::getSaveFormAction()->label('Lưu thay đổi');
    }

    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()->label('Hủy');
    }
}


