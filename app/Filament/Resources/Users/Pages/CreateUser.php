<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Filament\Traits\CheckPlanBeforeAccess;
use Filament\Resources\Pages\CreateRecord;
use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Model;

class CreateUser extends CreateRecord
{
    use CheckPlanBeforeAccess;

    protected static string $resource = UserResource::class;

    public function getTitle(): string
    {
        return __('admin.users.pages.create_title');
    }

    public function getBreadcrumbs(): array
    {
        return [
            url()->previous() => __('admin.users.model_label'),
            '' => __('admin.users.pages.create_title'),
        ];
    }
    public function mount(): void
    {
        parent::mount();
        $this->ensurePlanAccessible();
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()
            ->label(__('common.common_success.create'));
    }

    protected function getCreateAnotherFormAction(): Action
    {
        return parent::getCreateAnotherFormAction()
            ->label(__('common.common_success.create_and_create_another'));
    }

    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()
            ->label(__('common.common_success.cancel'));
    }

    protected function handleRecordCreation(array $data): Model
    {
        if (!empty($data['new_password'])) {
            $data['password'] = $data['new_password'];
        }

        if (!empty($data['verify_email'])) {
            $data['email_verified_at'] = now();
        } else {
            $data['email_verified_at'] = null;
        }

        unset($data['verify_email'], $data['new_password'], $data['new_password_confirmation']);

        return static::getModel()::create($data);
    }
}
