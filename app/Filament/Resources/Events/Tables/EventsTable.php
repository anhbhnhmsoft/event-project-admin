<?php

namespace App\Filament\Resources\Events\Tables;

use App\Filament\Resources\Events\EventResource;
use App\Models\Organizer;
use App\Utils\Constants\EventStatus;
use App\Utils\Helper;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
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
                ActionGroup::make([
                    ViewAction::make()
                        ->label('Xem'),
                    EditAction::make()
                        ->label('Sửa'),
                    Action::make('seats-manager')
                        ->label(__('event.pages.seats_title'))
                        ->icon('heroicon-o-building-office')
                        ->url(fn($record) => EventResource::getUrl('seats-manage', ['record' => $record]))
                        ->openUrlInNewTab()
                        ->color('success'),
                    Action::make('quick-register')
                        ->label('Đăng ký nhanh')
                        ->icon('heroicon-o-qr-code')
                        ->color('primary')
                        ->modalHeading('QR Code Đăng ký nhanh')
                        ->modalContent(fn($record) => view('filament.event.quick-register-qr', [
                            'event' => $record,
                            'url' => Helper::quickRegisterUrl($record)
                        ]))
                        ->modalSubmitAction(false)
                        ->modalCancelAction(fn(Action $action) => $action->label('Đóng')),
                    Action::make('comments-manager')
                        ->label('Quản lý bình luận')
                        ->icon('heroicon-o-chat-bubble-bottom-center-text')
                        ->url(fn($record) => EventResource::getUrl('comments-manage', ['record' => $record]))
                        ->openUrlInNewTab()
                        ->color('success'),
                    Action::make('games-manager')
                        ->label('Quản lý trò chơi')
                        ->icon('heroicon-o-cube')
                        ->url(fn($record) => EventResource::getUrl('games-manage', ['record' => $record]))
                        ->openUrlInNewTab()
                        ->color('primary'),
                    // Action::make('games-manager')
                    //     ->label('Quản lý khảo sát/bình chọn')
                    //     ->icon('heroicon-o-cube')
                    //     ->url(fn($record) => EventResource::getUrl('votes-manage', ['record' => $record]))
                    //     ->openUrlInNewTab()
                    //     ->color('success'),
                ])
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
