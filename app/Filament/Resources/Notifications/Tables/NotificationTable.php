<?php

namespace App\Filament\Resources\Notifications\Tables;

use App\Utils\Constants\UserNotificationStatus;
use App\Utils\Constants\UserNotificationType;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class NotificationTable
{
    public static function make(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('user.name')->label(__('admin.notifications.table.user'))->toggleable(),
                TextColumn::make('title')->label(__('admin.notifications.table.title'))->wrap()->toggleable(),
                BadgeColumn::make('notification_type')
                    ->label(__('admin.notifications.table.notification_type'))
                    ->formatStateUsing(fn($state) => UserNotificationType::from((int) $state)->label())
                    ->colors(['primary']),
                BadgeColumn::make('status')
                    ->label(__('admin.notifications.table.status'))
                    ->formatStateUsing(fn($state) => UserNotificationStatus::from((int) $state)->label())
                    ->colors([
                        'warning' => fn($state) => (int) $state === UserNotificationStatus::SENT->value,
                        'success' => fn($state) => (int) $state === UserNotificationStatus::READ->value,
                        'danger' => fn($state) => (int) $state === UserNotificationStatus::FAILED->value,
                    ]),
            ])
            ->filters([
                SelectFilter::make('notification_type')->label(__('admin.notifications.table.notification_type'))->options(UserNotificationType::getOptions()),
                SelectFilter::make('status')->label(__('admin.notifications.table.status'))->options(UserNotificationStatus::getOptions()),
                Filter::make('created_at')
                    ->schema([
                        Forms\Components\DatePicker::make('from')->label(__('admin.notifications.table.from_date')),
                        Forms\Components\DatePicker::make('to')->label(__('admin.notifications.table.to_date')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'] ?? null, fn(Builder $q, $date) => $q->whereDate('created_at', '>=', $date))
                            ->when($data['to'] ?? null, fn(Builder $q, $date) => $q->whereDate('created_at', '<=', $date));
                    }),
            ])
            ->recordActions([
                Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->modalHeading(__('admin.notifications.table.recipients'))
                    ->modalWidth('4xl')
                    ->modalSubmitAction(false)
                    ->modalContent(fn($record) => view('filament.modals.notifications.recipients', [
                        'record' => $record->load('user'),
                    ]))
                    ->color('primary'),
            ]);
    }
}
