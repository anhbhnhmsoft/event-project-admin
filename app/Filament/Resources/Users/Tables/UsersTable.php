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

                SelectFilter::make('email_verified')
                    ->label('Trạng thái email')
                    ->options([
                        'verified' => 'Đã xác thực',
                        'unverified' => 'Chưa xác thực',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (!isset($data['value'])) {
                            return $query;
                        }

                        return $data['value'] === 'verified'
                            ? $query->whereNotNull('email_verified_at')
                            : $query->whereNull('email_verified_at');
                    })
                    ->placeholder('Tất cả'),
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
