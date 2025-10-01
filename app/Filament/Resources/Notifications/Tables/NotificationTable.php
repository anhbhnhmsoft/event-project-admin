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
                TextColumn::make('user.name')->label('Người nhận')->toggleable(),
                TextColumn::make('title')->label('Tiêu đề')->wrap()->toggleable(),
                BadgeColumn::make('notification_type')
                    ->label('Loại')
                    ->formatStateUsing(fn ($state) => UserNotificationType::from((int) $state)->label())
                    ->colors(['primary']),
                BadgeColumn::make('status')
                    ->label('Trạng thái')
                    ->formatStateUsing(fn ($state) => UserNotificationStatus::from((int) $state)->label())
                    ->colors([
                        'warning' => fn ($state) => (int) $state === UserNotificationStatus::SENT->value,
                        'success' => fn ($state) => (int) $state === UserNotificationStatus::READ->value,
                        'danger' => fn ($state) => (int) $state === UserNotificationStatus::FAILED->value,
                    ]),
            ])
            ->filters([
                SelectFilter::make('notification_type')->label('Loại')->options(UserNotificationType::getOptions()),
                SelectFilter::make('status')->label('Trạng thái')->options(UserNotificationStatus::getOptions()),
                Filter::make('created_at')
                    ->schema([
                        Forms\Components\DatePicker::make('from')->label('Từ ngày'),
                        Forms\Components\DatePicker::make('to')->label('Đến ngày'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'] ?? null, fn (Builder $q, $date) => $q->whereDate('created_at', '>=', $date))
                            ->when($data['to'] ?? null, fn (Builder $q, $date) => $q->whereDate('created_at', '<=', $date));
                    }),
            ])
            ->recordActions([
                Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->modalHeading('Danh sách người nhận')
                    ->modalWidth('4xl')
                    ->modalSubmitAction(false)
                    ->modalContent(fn ($record) => view('filament.modals.notifications.recipients-livewire', [
                        'record' => $record,
                    ]))
                    ->color('primary'),
            ]);
    }
}


