<?php

namespace App\Filament\Resources\OrganizersAdmin\Tables;

use App\Utils\Constants\CommonStatus;
use App\Utils\Helper;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OrganizerAdminTable
{
    public static function configure(Table $table): Table
    {
        return $table->columns([
            ImageColumn::make('image')
                ->label(__('admin.organizers.table.image'))
                ->disk('public')
                ->width(100)
                ->state(function ($record) {
                    return !empty($record->image)
                        ? Helper::generateURLImagePath($record->image)
                        : null;
                }),
            TextColumn::make('name')
                ->label(__('admin.organizers.table.name'))
                ->limit(30)
                ->tooltip(function ($column): ?string {
                    $state = $column->getState();
                    if (strlen($state) <= $column->getCharacterLimit()) {
                        return null;
                    }
                    return $state;
                })
                ->searchable(),
            TextColumn::make('status')
                ->label(__('admin.organizers.table.status'))
                ->badge()
                ->color(fn($state) => match ($state) {
                    CommonStatus::ACTIVE->value => 'success',
                    CommonStatus::INACTIVE->value => 'warning'
                })->formatStateUsing(fn($state) => $state == CommonStatus::ACTIVE->value ? __('admin.organizers.table.active') : __('admin.organizers.table.inactive')),
            TextColumn::make('created_at')->label(__('admin.organizers.table.created_at'))->dateTime('d/m/Y H:i'),
        ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()
                    ->label(__('common.common_success.edit')),
                DeleteAction::make()
                    ->label(__('common.common_success.delete')),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label(__('common.common_success.delete')),
                ]),
            ]);;
    }
}


