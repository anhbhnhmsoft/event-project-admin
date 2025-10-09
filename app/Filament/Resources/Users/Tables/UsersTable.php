<?php

namespace App\Filament\Resources\Users\Tables;

use App\Utils\Helper;
use App\Utils\Constants\RoleUser;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                ImageColumn::make('avatar_path')
                    ->label('Ảnh đại diện')
                    ->circular()
                    ->disk('public')
                    ->state(function ($record) {
                        if (! empty($record->avatar_path)) {
                            return Helper::generateURLImagePath($record->avatar_path);
                        }
                        return Helper::generateUiAvatarUrl($record->name, $record->email);
                    }),
                TextColumn::make('name')
                    ->label('Tên người dùng')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
                TextColumn::make('phone')
                    ->label('Số điện thoại')
                    ->searchable(),
                TextColumn::make('address')
                    ->label('Địa chỉ')
                    ->searchable(),
                TextColumn::make('organizer.name')
                    ->label('Nhà tổ chức')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Sửa')
                    ->visible(fn() => Auth::user()->role === RoleUser::SUPER_ADMIN->value ||
                        Auth::user()->role === RoleUser::ADMIN->value),
                DeleteAction::make()
                    ->label('Xóa')
                    ->visible(fn() => Auth::user()->role === RoleUser::SUPER_ADMIN->value),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Xóa')
                        ->visible(fn() => Auth::user()->role === RoleUser::SUPER_ADMIN->value),
                ]),
            ]);
    }
}
