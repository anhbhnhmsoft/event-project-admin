<?php

namespace App\Filament\Resources\Memberships\Tables;

use App\Utils\Constants\ConfigMembership;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MembershipsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Tên gói'),
                TextColumn::make('badge')
                    ->label('Huy hiệu hiển thị'),
                TextColumn::make('price')
                    ->label('Giá'),
                IconColumn::make('status')
                    ->label("Trạng thái hoạt động")
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                TextColumn::make('duration')
                    ->label('Thời hạn gói'),
                IconColumn::make('config.'.ConfigMembership::ALLOW_COMMENT->value)
                    ->label(ConfigMembership::ALLOW_COMMENT->label())->boolean()
                    ->trueIcon('heroicon-o-check')
                    ->falseIcon('heroicon-o-x-mark')
                    ->trueColor('success')
                    ->falseColor('gray'),
                IconColumn::make('config.'.ConfigMembership::ALLOW_CHOOSE_SEAT->value)
                    ->label(ConfigMembership::ALLOW_CHOOSE_SEAT->label())->boolean()
                    ->trueIcon('heroicon-o-check')
                    ->falseIcon('heroicon-o-x-mark')
                    ->trueColor('success')
                    ->falseColor('gray'),
                IconColumn::make('config.'.ConfigMembership::ALLOW_DOCUMENTARY->value)
                    ->label(ConfigMembership::ALLOW_DOCUMENTARY->label())
                    ->boolean()
                    ->trueIcon('heroicon-o-check')
                    ->falseIcon('heroicon-o-x-mark')
                    ->trueColor('success')
                    ->falseColor('gray')
            ])
            ->filters([])
            ->recordActions([
                EditAction::make()
                    ->label("Sửa"),
                DeleteAction::make()
                    ->label('Xóa')
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Xóa'),
                ]),
            ]);
    }
}
