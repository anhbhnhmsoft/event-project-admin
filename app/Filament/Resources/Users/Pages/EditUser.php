<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Filament\Traits\CheckPlanBeforeAccess;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions\Action;

class EditUser extends EditRecord
{
    use CheckPlanBeforeAccess;

    protected static string $resource = UserResource::class;

    public function getTitle(): string
    {
        return __('admin.users.pages.edit_title');
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    public function getBreadcrumbs(): array
    {
        return [
            url()->previous() => __('admin.users.model_label'),
            '' => __('admin.users.pages.edit_title'),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (!empty($data['verify_email'])) {
            $data['email_verified_at'] = now();
        } else {
            $data['email_verified_at'] = null;
        }

        if (!empty($data['new_password'])) {
            $data['password'] = bcrypt($data['new_password']);
        }

        unset($data['verify_email'], $data['new_password'], $data['new_password_confirmation'], $data['showChangePassword']);

        return $data;
    }

    protected function getSaveFormAction(): Action
    {
        return parent::getSaveFormAction()
            ->label(__('common.common_success.save'));
    }

    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()
            ->label(__('common.common_success.cancel'));
    }
}
