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

    public function getTitle(): string
    {
        return __('admin.users.pages.list_title');
    }

    protected function getHeaderActions(): array
    {
        $user = Auth::user();

        if ($user->role === RoleUser::SUPER_ADMIN->value || $user->role === RoleUser::ADMIN->value) {
            return [ 
                CreateAction::make()
                ->label(__('admin.users.pages.create_title'))
            ];
        }
        
        return [];
    }
    public function getBreadcrumbs(): array
    {
        return [
            url()->previous() => __('admin.users.model_label'),
            '' => __('admin.users.pages.list_title'),
        ];
    }
}
