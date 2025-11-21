<?php

namespace App\Filament\Resources\OrganizersAdmin\Pages;

use App\Filament\Resources\OrganizersAdmin\OrganizerAdminResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions\Action;

class EditOrganizerAdmin extends EditRecord
{
    protected static string $resource = OrganizerAdminResource::class;

    public function getTitle(): string
    {
        return __('admin.organizers.pages.edit_title');
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label(__('common.common_success.delete')),
        ];
    }
    public function getBreadcrumbs(): array
    {
        return [
            url()->previous() => __('admin.organizers.model_label'),
            '' => __('admin.organizers.pages.edit_title'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    protected function getSaveFormAction(): Action
    {
        return parent::getSaveFormAction()->label(__('common.common_success.save'));
    }

    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()->label(__('common.common_success.cancel'));
    }
}
