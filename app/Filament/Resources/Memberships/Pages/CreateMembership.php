<?php

namespace App\Filament\Resources\Memberships\Pages;

use Filament\Actions\Action;
use App\Filament\Resources\Memberships\MembershipResource;
use App\Filament\Traits\CheckPlanBeforeAccess;
use App\Utils\Constants\MembershipType;
use App\Utils\Constants\RoleUser;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateMembership extends CreateRecord
{
    use CheckPlanBeforeAccess;

    protected static string $resource = MembershipResource::class;

    public function getTitle(): string
    {
        return __('admin.memberships.pages.create_title');
    }

    public function getBreadcrumbs(): array
    {
        return [
            url()->previous() => __('admin.memberships.model_label'),
            '' => __('admin.memberships.pages.create_title'),
        ];
    }

    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()->label(__('common.common_success.create'));
    }

    protected function getCreateAnotherFormAction(): Action
    {
        return parent::getCreateAnotherFormAction()
            ->label(__('common.common_success.create_and_create_another'));
    }

    public function mount(): void
    {
        parent::mount();
        $this->ensurePlanAccessible();
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = Auth::user();

        if ($user->role == RoleUser::ADMIN->value) {
            $data['type']  = MembershipType::FOR_CUSTOMER->value;
        };
        $data['organizer_id'] = $user->organizer_id;
        return $data;
    }

    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()->label(__('common.common_success.cancel'));
    }
}
