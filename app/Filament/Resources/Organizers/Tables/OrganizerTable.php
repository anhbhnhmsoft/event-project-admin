<?php

namespace App\Filament\Resources\Organizers\Tables;

use App\Utils\Constants\CommonStatus;
use App\Utils\Helper;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OrganizerTable
{
    public static function configure(Table $table): Table
    {
        return $table->columns([
            ImageColumn::make('image')
                ->label('Ảnh')
                ->disk('public')
                ->width(100)
                ->state(function ($record) {
                    return !empty($record->image)
                        ? Helper::generateURLImagePath($record->image)
                        : null;
                }),
            TextColumn::make('name')->label('Tên')->searchable(),
            TextColumn::make('status')
                    ->label('Trạng thái')
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        CommonStatus::ACTIVE->value => 'success',
                        CommonStatus::INACTIVE->value => 'warning'
                    })->formatStateUsing(fn($state) => $state == CommonStatus::ACTIVE->value ? 'Hoạt động' : 'Không hoạt động'),
            TextColumn::make('created_at')->label('Tạo lúc')->dateTime('d/m/Y H:i'),
        ])
        ->filters([
            //
        ])
        ->recordActions([
            EditAction::make()
            ->label('Sửa'),
            DeleteAction::make()
            ->label('Xóa'),
        ])
        ->toolbarActions([
            BulkActionGroup::make([
                DeleteBulkAction::make()
                ->label('Xóa'),
            ]),
        ]);;
    }
}


