<?php

namespace App\Filament\Resources\Organizers;

use App\Filament\Resources\Organizers\Pages\CreateOrganizer;
use App\Filament\Resources\Organizers\Pages\EditOrganizer;
use App\Filament\Resources\Organizers\Pages\ListOrganizers;
use App\Models\Organizer;
use App\Filament\Resources\Organizers\Schemas\OrganizerSchema;
use App\Filament\Resources\Organizers\Tables\OrganizerTable;
use App\Utils\Constants\RoleUser;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;

class OrganizerResource extends Resource
{
    protected static ?string $model = Organizer::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice;
    protected static ?string $navigationLabel = 'Nhà tổ chức';
    protected static ?string $modelLabel = 'Nhà tổ chức';
    protected static ?string $pluralModelLabel = 'Nhà tổ chức';

    public static function form(Schema $schema): Schema
    {
        return OrganizerSchema::configure($schema);
    }

    public static function table(\Filament\Tables\Table $table): \Filament\Tables\Table
    {
        return OrganizerTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOrganizers::route('/'),
            'create' => CreateOrganizer::route('/create'),
            'edit' => EditOrganizer::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        $user = Auth::user();
        return $user->role === RoleUser::SUPER_ADMIN->value;
    }
}


