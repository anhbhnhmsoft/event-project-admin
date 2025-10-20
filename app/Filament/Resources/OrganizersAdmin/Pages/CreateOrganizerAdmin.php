<?php

namespace App\Filament\Resources\OrganizersAdmin\Pages;

use App\Filament\Resources\OrganizersAdmin\OrganizerAdminResource;
use App\Services\OrganizerService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

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

    protected function handleRecordCreation(array $data): Model
    {
        $organizerService = app(OrganizerService::class);
        $result = $organizerService->initOrganizer($data);

        if (!$result['status']) {
            Notification::make()
                ->title('Tạo tổ chức thất bại!')
                ->body($result['message'])
                ->danger()
                ->send();
        }
        return $result['data'] ?? $this->getRecord();
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
