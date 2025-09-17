<?php

namespace App\Filament\Resources\Memberships\Tables;

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
                IconColumn::make('config.allow_comment')
                    ->label('Cho phép bình luận')->boolean()
                    ->trueIcon('heroicon-o-check')
                    ->falseIcon('heroicon-o-x-mark')
                    ->trueColor('success')
                    ->falseColor('gray'),
                IconColumn::make('config.allow_choose_seat')
                    ->label('Cho phép chọn chỗ ngồi')->boolean()
                    ->trueIcon('heroicon-o-check')
                    ->falseIcon('heroicon-o-x-mark')
                    ->trueColor('success')
                    ->falseColor('gray'),
                IconColumn::make('config.allow_documentary')
                    ->label('Cho phép xem hay tải xuống tài liệu')
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
