<?php

namespace App\Filament\Resources\Organizers\Pages;

use App\Filament\Resources\Organizers\OrganizerResource;
use App\Models\Organizer;
use App\Services\OrganizerService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateOrganizer extends CreateRecord
{
    protected static string $resource = OrganizerResource::class;

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
