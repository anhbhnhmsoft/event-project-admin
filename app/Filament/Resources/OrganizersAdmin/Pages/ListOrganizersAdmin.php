<?php

namespace App\Filament\Resources\OrganizersAdmin\Pages;

use App\Filament\Resources\OrganizersAdmin\OrganizerAdminResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListOrganizersAdmin extends ListRecords
{
    protected static string $resource = OrganizerAdminResource::class;

    public function getTitle(): string
    {
        return __('admin.organizers.pages.list_title');
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label(__('common.common_success.create')),
        ];
    }

    public function getBreadcrumbs(): array
    {
        return [
            url()->previous() => __('admin.organizers.model_label'),
            '' => __('admin.organizers.pages.list_title'),
        ];
    }
}


