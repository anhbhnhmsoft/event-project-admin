<?php

namespace App\Filament\Resources\Notifications;

use App\Filament\Resources\Notifications\Pages;
use App\Filament\Resources\Notifications\Schemas\NotificationSchema;
use App\Filament\Resources\Notifications\Tables\NotificationTable;
use App\Models\User;
use App\Models\UserNotification;
use App\Utils\Constants\RoleUser;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class NotificationResource extends Resource
{
    protected static ?string $model = UserNotification::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-bell';

    protected static \UnitEnum|string|null $navigationGroup = 'Hệ thống';

    protected static ?string $navigationLabel = 'Thông báo';

    public static function form(Schema $schema): Schema
    {
        return NotificationSchema::make($schema);
    }

    public static function table(Table $table): Table
    {
        return NotificationTable::make($table);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = Auth::user();
        if ($user->role === RoleUser::ADMIN->value) {
            $organizerId = $user->organizer_id;
            $userIds = User::query()
                ->where('organizer_id', $organizerId)
                ->pluck('id');
            $query->whereIn('user_id', $userIds);
        }

        return $query;
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNotifications::route('/'),
            'create' => Pages\CreateNotification::route('/create'),
            'view' => Pages\ViewNotification::route('/{record}/view'),
        ];
    }

    public static function canAccess(): bool
    {
        $user = Auth::user();
        return $user->role === RoleUser::ADMIN->value || $user->role === RoleUser::SUPER_ADMIN->value;
    }
}


