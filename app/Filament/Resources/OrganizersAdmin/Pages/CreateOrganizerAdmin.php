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

    public function getTitle(): string
    {
        return __('admin.organizers.pages.create_title');
    }

    public function getBreadcrumbs(): array
    {
        return [
            url()->previous() => __('admin.organizers.model_label'),
            '' => __('admin.organizers.pages.create_title'),
        ];
    }

    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()->label(__('common.common_success.create'));
    }

    protected function handleRecordCreation(array $data): Model
    {
        $organizerService = app(OrganizerService::class);
        $result = $organizerService->initOrganizer($data);

        if (!$result['status']) {
            Notification::make()
                ->title(__('admin.organizers.notifications.create_failed'))
                ->body($result['message'])
                ->danger()
                ->send();
        }
        return $result['data'] ?? $this->getRecord();
    }

    protected function getCreateAnotherFormAction(): Action
    {
        return parent::getCreateAnotherFormAction()
            ->label(__('common.common_success.create_and_create_another'));
    }

    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()->label(__('common.common_success.cancel'));
    }
}
