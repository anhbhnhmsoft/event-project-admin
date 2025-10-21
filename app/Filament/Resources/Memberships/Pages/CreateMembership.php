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

    protected static ?string $title = 'Tạo gói thành viên';

    public function getBreadcrumbs(): array
    {
        return [
            url()->previous() => 'Gói thành viên',
            '' => 'Tạo gói thành viên',
        ];
    }

    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()->label('Tạo mới');
    }

    protected function getCreateAnotherFormAction(): Action
    {
        return parent::getCreateAnotherFormAction()
            ->label('Tạo và tạo thêm');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = Auth::user();

        if ($user->role == RoleUser::ADMIN->value) {
            $type = MembershipType::FOR_CUSTOMER->value;
        };
        $data['type']        = $type;
        $data['organizer_id'] = $user->organizer_id;
        return $data;
    }

    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()->label('Hủy');
    }
}
