<?php

namespace App\Filament\Resources\Memberships\Pages;

use App\Filament\Resources\Memberships\MembershipResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMemberships extends ListRecords
{
    protected static string $resource = MembershipResource::class;

    public function getTitle(): string
    {
        return __('admin.memberships.pages.list_title');
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
            url()->previous() => __('admin.memberships.model_label'),
            '' => __('admin.memberships.pages.list_title'),
        ];
    }
}
