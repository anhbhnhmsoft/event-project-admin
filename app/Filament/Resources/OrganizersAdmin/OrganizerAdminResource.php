<?php

namespace App\Filament\Resources\OrganizersAdmin;

use App\Filament\Resources\OrganizersAdmin\Pages\CreateOrganizerAdmin;
use App\Filament\Resources\OrganizersAdmin\Pages\EditOrganizerAdmin;
use App\Filament\Resources\OrganizersAdmin\Pages\ListOrganizersAdmin;
use App\Models\Organizer;
use App\Filament\Resources\OrganizersAdmin\Schemas\OrganizerAdminSchema;
use App\Filament\Resources\OrganizersAdmin\Tables\OrganizerAdminTable;
use App\Utils\Constants\RoleUser;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;

class OrganizerAdminResource extends Resource
{
    protected static ?string $model = Organizer::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice;
    public static function getNavigationLabel(): string
    {
        return __('admin.organizers.label');
    }
    public static function getModelLabel(): string
    {
        return __('admin.organizers.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.organizers.label');
    }

    public static function form(Schema $schema): Schema
    {
        return OrganizerAdminSchema::configure($schema);
    }

    public static function table(\Filament\Tables\Table $table): \Filament\Tables\Table
    {
        return OrganizerAdminTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOrganizersAdmin::route('/'),
            'create' => CreateOrganizerAdmin::route('/create'),
            'edit' => EditOrganizerAdmin::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        $user = Auth::user();
        return $user->role === RoleUser::SUPER_ADMIN->value;
    }
}


