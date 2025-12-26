<?php

namespace App\Filament\Resources\OrganizersAdmin\Tables;

use App\Utils\Constants\CommonStatus;
use App\Utils\Constants\MembershipUserStatus;
use App\Utils\Helper;
use Carbon\Carbon;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
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

            TextColumn::make('created_at')->label(__('admin.organizers.table.created_at'))->dateTime('d/m/Y H:i'),
            TextColumn::make('plans')
                ->label(__('admin.members.columns.memberships'))
                ->formatStateUsing(function ($state) {
                    if (!$state) return null;
                    $endDate = $state->pivot->end_date;
                    return $state->name . ': ' .
                        MembershipUserStatus::from($state->pivot->status)->label() .
                        ' : ' . __('admin.members.columns.time_expired') . ' : ' .
                        ($endDate ?? Carbon::parse($endDate)->format('d/m/Y'));
                })
                ->listWithLineBreaks()
                ->bulleted()
                ->searchable(),
            ToggleColumn::make('status')
                ->label(__('admin.organizers.table.status')),
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


