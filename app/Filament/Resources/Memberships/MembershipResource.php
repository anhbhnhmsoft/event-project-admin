<?php

namespace App\Filament\Resources\Memberships;

use App\Filament\Resources\Memberships\Pages\CreateMembership;
use App\Filament\Resources\Memberships\Pages\EditMembership;
use App\Filament\Resources\Memberships\Pages\ListMemberships;
use App\Filament\Resources\Memberships\Schemas\MembershipSchema;
use App\Filament\Resources\Memberships\Tables\MembershipsTable;
use App\Models\Membership;
use App\Utils\Constants\RoleUser;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class MembershipResource extends Resource
{
    protected static ?string $model = Membership::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Users;

    protected static ?string $navigationLabel = 'Gói thành viên';
    protected static ?string $modelLabel = 'Gói thành viên';
    protected static ?string $pluralModelLabel = 'Gói thành viên';


    public static function form(Schema $form): Schema
    {
        return MembershipSchema::configure($form);
    }

    public static function table(Table $table): Table
    {
        return MembershipsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMemberships::route('/'),
            'create' => CreateMembership::route('/create'),
            'edit' => EditMembership::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        $user = Auth::user();
        return $user->role === RoleUser::SUPER_ADMIN->value|| $user->role === RoleUser::ADMIN->value;
    }
}
