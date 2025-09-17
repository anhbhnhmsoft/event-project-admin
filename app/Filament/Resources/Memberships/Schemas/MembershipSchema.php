<?php

namespace App\Filament\Resources\Memberships\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\RichEditor;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class MembershipSchema
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Thông tin gói')
                    ->schema([
                        TextInput::make('name')
                            ->label('Tên gói')
                            ->required(),
                        TextInput::make('price')
                            ->label('Giá')
                            ->minValue(0)
                            ->required()
                            ->helperText('Đơn vị: VND')
                            ->numeric(),
                        TextInput::make('duration')
                            ->label('Thời gian sử dụng')
                            ->numeric()
                            ->placeholder('Bao nhiêu tháng')
                            ->minValue(0)
                            ->required(),
                        TextInput::make('sort')
                            ->label('Sắp xếp')
                            ->helperText("Số càng nhỏ, gói sẽ hiển thị càng cao trong danh sách")
                            ->integer()
                            ->minValue(0)
                            ->required(),
                        RichEditor::make('description')
                            ->label('Miêu tả')
                    ]),
                Section::make()->schema([
                    Section::make('cấu hình hiển thị')
                        ->schema([
                            TextInput::make('badge')
                                ->label('Huy hiệu gói thành viên')
                                ->maxLength(255),
                            ColorPicker::make('badge_color_background')
                                ->label('Màu huy hiệu trên trang chủ'),
                            ColorPicker::make('badge_color_text')
                                ->label('Màu chữ huy hiệu trên trang chủ'),
                            Toggle::make('status')
                                ->label('Trạng thái kích hoạt')
                                ->required(),
                        ]),
                    Section::make('Cấu hình quyền')
                        ->schema([
                            Toggle::make('config.allow_comment')
                                ->label("Cho phép bình luận"),
                            Toggle::make('config.allow_choose_seat')
                                ->label("Cho phép chọn chỗ ngồi"),
                            Toggle::make('config.allow_documentary')
                                ->label("Cho phép xem tải hay xem tài liệu trong sự kiện"),
                        ])
                ])
            ]);
    }
}
