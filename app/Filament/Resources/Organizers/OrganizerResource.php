<?php

namespace App\Filament\Resources\Organizers;

use App\Filament\Resources\Organizers\Pages\EditOrganizer;
use App\Models\Organizer;
use App\Utils\Constants\RoleUser;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;

class OrganizerResource extends Resource
{
    protected static ?string $model = Organizer::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice;
    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.system');
    }
    public static function getNavigationLabel(): string
    {
        return __('admin.organizers.label_admin_organizer');
    }
    public static function getModelLabel(): string
    {
        return __('admin.organizers.label_admin_organizer');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.organizers.label_admin_organizer');
    }

    public static function getPages(): array
    {
        return [
            'index' => EditOrganizer::route('/'),
        ];
    }

    public static function canAccess(): bool
    {
        $user = Auth::user();
        return $user->role === RoleUser::ADMIN->value;
    }
}
