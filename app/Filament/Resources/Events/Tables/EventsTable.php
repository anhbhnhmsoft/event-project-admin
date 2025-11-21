<?php

namespace App\Filament\Resources\Events\Tables;

use App\Filament\Resources\Events\EventResource;
use App\Models\Organizer;
use App\Utils\Constants\EventStatus;
use App\Utils\Helper;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class EventsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image_represent_path')
                    ->label(__('admin.events.table.image'))
                    ->disk('public')
                    ->imageSize(60)
                    ->visibility('public'),
                TextColumn::make('name')
                    ->label(__('admin.events.table.name'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('organizer.name')
                    ->label(__('admin.events.table.organizer'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('start_time')
                    ->label(__('admin.events.table.start_time'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('end_time')
                    ->label(__('admin.events.table.end_time'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('status')
                    ->label(__('admin.events.table.status'))
                    ->badge()
                    ->color(fn($state) => EventStatus::tryFrom($state)?->color() ?? 'gray')
                    ->formatStateUsing(fn($state) => EventStatus::tryFrom($state)?->label() ?? __('admin.events.table.unknown')),
                TextColumn::make('province.name')
                    ->label(__('admin.events.table.province'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('admin.events.table.created_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('organizer_id')
                    ->label(__('admin.events.table.organizer'))
                    ->options(Organizer::pluck('name', 'id'))
                    ->searchable(),
                SelectFilter::make('status')
                    ->label(__('admin.events.table.status'))
                    ->options(EventStatus::getOptions()),
                TrashedFilter::make(),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()
                        ->label(__('common.common_success.view')),
                    EditAction::make()
                        ->label(__('common.common_success.edit')),
                    Action::make('seats-manager')
                        ->label(__('admin.events.pages.seats_title'))
                        ->icon('heroicon-o-building-office')
                        ->url(fn($record) => EventResource::getUrl('seats-manage', ['record' => $record]))
                        ->openUrlInNewTab()
                        ->color('success'),
                    Action::make('quick-register')
                        ->label(__('admin.events.table.quick_register'))
                        ->icon('heroicon-o-qr-code')
                        ->color('primary')
                        ->modalHeading(__('admin.events.table.qr_code_heading'))
                        ->modalContent(fn($record) => view('filament.event.quick-register-qr', [
                            'event' => $record,
                            'url' => Helper::quickRegisterUrl($record)
                        ]))
                        ->modalSubmitAction(false)
                        ->modalCancelAction(fn(Action $action) => $action->label(__('common.common_success.close'))),
                    Action::make('check-in')
                        ->label('Check-in QR')
                        ->icon('heroicon-o-qr-code')
                        ->color('success')
                        ->modalHeading('Check-in QR Code')
                        ->modalContent(fn($record) => view('filament.event.quick-register-qr', [
                            'event' => $record,
                            'url' => Helper::quickCheckinUrl($record)
                        ]))
                        ->modalSubmitAction(false)
                        ->modalCancelAction(fn(Action $action) => $action->label(__('common.common_success.close'))),
                    Action::make('comments-manager')
                        ->label(__('admin.events.table.manage_comments'))
                        ->icon('heroicon-o-chat-bubble-bottom-center-text')
                        ->url(fn($record) => EventResource::getUrl('comments-manage', ['record' => $record]))
                        ->openUrlInNewTab()
                        ->color('success'),
                    Action::make('games-manager')
                        ->label(__('admin.events.table.manage_games'))
                        ->icon('heroicon-o-cube')
                        ->url(fn($record) => EventResource::getUrl('games-manage', ['record' => $record]))
                        ->openUrlInNewTab()
                        ->color('primary'),
                    Action::make('votes-manager')
                        ->label(__('admin.events.table.manage_votes'))
                        ->icon('heroicon-o-cube')
                        ->url(fn($record) => EventResource::getUrl('votes-manage', ['record' => $record]))
                        ->openUrlInNewTab()
                        ->color('success'),
                    Action::make('speaker-screen')
                        ->label(__('admin.events.table.event_screen'))
                        ->icon('heroicon-o-cube')
                        ->url(fn($record) => EventResource::getUrl('speaker-screen', ['record' => $record]))
                        ->openUrlInNewTab()
                        ->color('primary'),
                    Action::make('check-in')
                        ->label('Check-in QR')
                        ->icon('heroicon-o-qr-code')
                        ->color('success')
                        ->modalHeading('Check-in QR Code')
                        ->modalContent(fn($record) => view('filament.event.quick-register-qr', [
                            'event' => $record,
                            'url' => Helper::quickCheckinUrl($record)
                        ]))
                        ->modalSubmitAction(false)
                        ->modalCancelAction(fn(Action $action) => $action->label(__('common.common_success.close'))),
                ])
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label(__('common.common_success.delete')),
                    ForceDeleteBulkAction::make()
                        ->label(__('admin.events.table.force_delete')),
                    RestoreBulkAction::make()
                        ->label(__('admin.events.table.restore')),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
