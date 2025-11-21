<?php

namespace App\Filament\Resources\Users\Tables;

use App\Models\Organizer;
use App\Utils\Helper;
use App\Utils\Constants\RoleUser;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        $currentUser = Auth::user();
        $isSuperAdmin = $currentUser->role === RoleUser::SUPER_ADMIN->value;
        $isAdmin = $currentUser->role === RoleUser::ADMIN->value || $isSuperAdmin;
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                ImageColumn::make('avatar_path')
                    ->label(__('admin.users.table.avatar'))
                    ->circular()
                    ->disk('public')
                    ->state(function ($record) {
                        if (!empty($record->avatar_path)) {
                            return Helper::generateURLImagePath($record->avatar_path);
                        }
                        return Helper::generateUiAvatarUrl($record->name, $record->email);
                    }),
                TextColumn::make('name')
                    ->label(__('admin.users.table.name'))
                    ->limit(30)
                    ->tooltip(function ($column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= $column->getCharacterLimit()) {
                            return null;
                        }
                        return $state;
                    })
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->limit(30)
                    ->tooltip(function ($column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= $column->getCharacterLimit()) {
                            return null;
                        }
                        return $state;
                    })
                    ->searchable(),
                TextColumn::make('phone')
                    ->label(__('admin.users.table.phone'))
                    ->searchable(),
                TextColumn::make('organizer.name')
                    ->label(__('admin.users.table.organizer'))
                    ->visible($isSuperAdmin),
            ])
            ->filters([
                SelectFilter::make('role')
                    ->label(__('admin.users.table.role'))
                    ->options(RoleUser::getOptions())
                    ->placeholder(__('admin.users.table.all_roles')),

                SelectFilter::make('organizer_id')
                    ->label(__('admin.users.table.organizer'))
                    ->options(fn() => Organizer::query()->pluck('name', 'id'))
                    ->placeholder(__('admin.users.table.all_organizers'))
                    ->searchable()
                    ->visible($isSuperAdmin),
            ])
            ->recordActions([
                EditAction::make()
                    ->label(__('common.common_success.edit'))
                    ->disabled(!$isAdmin)
                    ->visible($isAdmin),
                DeleteAction::make()
                    ->label(__('common.common_success.delete'))
                    ->disabled(!$isAdmin)
                    ->visible($isAdmin),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label(__('common.common_success.delete'))
                        ->visible($isAdmin),
                ]),
            ]);
    }
}
