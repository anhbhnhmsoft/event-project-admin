<?php

namespace App\Filament\Resources\Memberships\Pages;

use App\Filament\Resources\Memberships\MembershipResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMemberships extends ListRecords
{
    protected static string $resource = MembershipResource::class;

    protected static ?string $title = 'Danh sách gói thành viên';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Tạo mới'),
        ];
    }

    public function getBreadcrumbs(): array
    {
        return [
            url()->previous() => 'Gói thành viên',
            '' => 'Danh sách',
        ];
    }
}
