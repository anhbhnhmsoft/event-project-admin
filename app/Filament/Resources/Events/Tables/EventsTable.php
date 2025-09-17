<?php

namespace App\Filament\Resources\Events\Tables;

use App\Models\Organizer;
use App\Utils\Constants\EventStatus;
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
                    ->label('Hình ảnh')
                    ->disk('public')
                    ->imageSize(60)
                    ->visibility('public'),
                TextColumn::make('name')
                    ->label('Tên sự kiện')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('organizer.name')
                    ->label('Nhà tổ chức')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('start_time')
                    ->label('Thời gian bắt đầu')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('end_time')
                    ->label('Thời gian kết thúc')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Trạng thái')
                    ->badge()
                    ->color(fn($state) => EventStatus::tryFrom($state)?->color() ?? 'gray')
                    ->formatStateUsing(fn($state) => EventStatus::tryFrom($state)?->label() ?? 'Không xác định'),
                TextColumn::make('province.name')
                    ->label('Tỉnh/Thành phố')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Ngày tạo')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('organizer_id')
                    ->label('Nhà tổ chức')
                    ->options(Organizer::pluck('name', 'id'))
                    ->searchable(),
                SelectFilter::make('status')
                    ->label('Trạng thái')
                    ->options(EventStatus::getOptions()),
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Xem'),
                EditAction::make()
                    ->label('Sửa'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Xóa'),
                    ForceDeleteBulkAction::make()
                        ->label('Xóa vĩnh viễn'),
                    RestoreBulkAction::make()
                        ->label('Khôi phục'),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
