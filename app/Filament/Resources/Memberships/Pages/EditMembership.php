<?php

namespace App\Filament\Resources\Memberships\Pages;

use App\Filament\Resources\Memberships\MembershipResource;
use App\Filament\Traits\CheckPlanBeforeAccess;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMembership extends EditRecord
{
    use CheckPlanBeforeAccess;
    protected static string $resource = MembershipResource::class;
    protected static ?string $title = 'Sửa gói thành viên';

    public function mount(string|int $record): void
    {
        parent::mount($record);
        $this->ensurePlanAccessible();
    }

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
            url()->previous() => 'Gói thành viên',
            '' => 'Sửa gói thành viên',
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
