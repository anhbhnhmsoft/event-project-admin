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

    protected static ?string $title = 'Tạo người dùng mới';

    public function getBreadcrumbs(): array
    {
        return [
            url()->previous() => 'Người dùng',
            '' => 'Tạo người dùng mới',
        ];
    }
    public function mount(): void
    {
        parent::mount();
        $this->ensurePlanAccessible();
    }
    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()
            ->label('Tạo mới');
    }

    protected function getCreateAnotherFormAction(): Action
    {
        return parent::getCreateAnotherFormAction()
            ->label('Tạo và tạo thêm');
    }

    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()
            ->label('Hủy');
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
