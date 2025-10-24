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
    public function getTitle(): string
    {
        return __('admin.memberships.pages.edit_title');
    }

    public function mount(string|int $record): void
    {
        parent::mount($record);
        $this->ensurePlanAccessible();
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
            url()->previous() => __('admin.memberships.model_label'),
            '' => __('admin.memberships.pages.edit_title'),
        ];
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
