<?php

namespace App\Filament\Resources\Events;

use App\Filament\Resources\Events\Pages\CreateEvent;
use App\Filament\Resources\Events\Pages\EditEvent;
use App\Filament\Resources\Events\Pages\EventComments;
use App\Filament\Resources\Events\Pages\EventGames;
use App\Filament\Resources\Events\Pages\EventVotes;
use App\Filament\Resources\Events\Pages\ListEvents;
use App\Filament\Resources\Events\Pages\SeatsEvent;
use App\Filament\Resources\Events\Schemas\EventForm;
use App\Filament\Resources\Events\Tables\EventsTable;
use App\Models\Event;
use App\Utils\Constants\RoleUser;
use App\Utils\Helper;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class EventResource extends Resource
{
    protected static ?string $model = Event::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $modelLabel = 'Sự kiện';
    protected static ?string $pluralModelLabel = 'Sự kiện';

    public static function form(Schema $schema): Schema
    {
        return EventForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EventsTable::configure($table);
    }
    public function mount(): void
    {
        parent::mount();
        Helper::checkPlanOrganizer();
    }

    public static function canAccess(): bool
    {
        $user = Auth::user();
        return in_array($user->role, [
            RoleUser::SUPER_ADMIN->value,
            RoleUser::ADMIN->value,
            RoleUser::SPEAKER->value,
        ]);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = Auth::user();

        if ($user->role === RoleUser::SUPER_ADMIN->value) {
            return $query;
        }

        return $query->where('organizer_id', $user->organizer_id);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEvents::route('/'),
            'create' => CreateEvent::route('/create'),
            'edit' => EditEvent::route('/{record}/edit'),
            'seats-manage' => SeatsEvent::route('/{record}/seats'),
            'comments-manage' => EventComments::route('/{record}/comments'),
            'games-manage'     => EventGames::route('/{record}/games'),
            'votes-manage'     => EventVotes::route('/{record}/votes'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
