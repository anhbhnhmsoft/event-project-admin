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
                    ->label('Ảnh đại diện')
                    ->circular()
                    ->disk('public')
                    ->state(function ($record) {
                        if (!empty($record->avatar_path)) {
                            return Helper::generateURLImagePath($record->avatar_path);
                        }
                        return Helper::generateUiAvatarUrl($record->name, $record->email);
                    }),
                TextColumn::make('name')
                    ->label('Tên người dùng')
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
                    ->label('Số điện thoại')
                    ->searchable(),
                TextColumn::make('organizer.name')
                    ->label('Nhà tổ chức')
                    ->visible($isSuperAdmin),
            ])
            ->filters([
                SelectFilter::make('role')
                    ->label('Vai trò')
                    ->options(RoleUser::getOptions())
                    ->placeholder('Tất cả vai trò'),

                SelectFilter::make('organizer_id')
                    ->label('Nhà tổ chức')
                    ->options(fn() => Organizer::query()->pluck('name', 'id'))
                    ->placeholder('Tất cả nhà tổ chức')
                    ->searchable()
                    ->visible($isSuperAdmin),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Sửa')
                    ->disabled(!$isAdmin)
                    ->visible($isAdmin),
                DeleteAction::make()
                    ->label('Xóa')
                    ->disabled(!$isAdmin)
                    ->visible($isAdmin),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Xóa')
                        ->visible($isAdmin),
                ]),
            ]);
    }
}
