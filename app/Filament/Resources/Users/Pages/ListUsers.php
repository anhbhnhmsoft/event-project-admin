<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Utils\Constants\RoleUser;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected static ?string $title = 'Danh sách người dùng';

    protected function getHeaderActions(): array
    {
        $user = Auth::user();

        if ($user->role === RoleUser::SUPER_ADMIN->value || $user->role === RoleUser::ADMIN->value) {
            return [ 
                CreateAction::make()
                ->label('Tạo người dùng mới')
            ];
        }
        
        return [];
    }
    public function getBreadcrumbs(): array
    {
        return [
            url()->previous() => 'Người dùng',
            '' => 'Danh sách',
        ];
    }
}
