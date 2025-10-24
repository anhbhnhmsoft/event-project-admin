<?php

namespace App\Filament\Resources\Memberships\Schemas;

use App\Utils\Constants\ConfigMembership;
use App\Utils\Constants\MembershipType;
use App\Utils\Constants\RoleUser;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Support\Facades\Auth;

class MembershipSchema
{
    public static function configure(Schema $schema): Schema
    {
        $isSuperAdmin = Auth::user()->role === RoleUser::SUPER_ADMIN->value;

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
                            ->helperText('Đơn vị: Tháng')
                            ->placeholder('Bao nhiêu tháng')
                            ->minValue(0)
                            ->required(),
                        TextInput::make('sort')
                            ->label('Sắp xếp')
                            ->helperText("Số càng nhỏ, gói sẽ hiển thị càng cao trong danh sách")
                            ->integer()
                            ->minValue(0)
                            ->required(),
                        Textarea::make('description')
                            ->required()
                            ->label('Miêu tả')
                    ]),
                Section::make()->schema([
                    Section::make('Cấu hình hiển thị')
                        ->schema([
                            TextInput::make('badge')
                                ->label('Huy hiệu gói thành viên')
                                ->maxLength(255),
                            Flex::make([
                                ColorPicker::make('badge_color_background')
                                    ->label('Màu nền huy hiệu trên trang chủ'),
                                ColorPicker::make('badge_color_text')
                                    ->label('Màu chữ huy hiệu trên trang chủ'),
                            ]),
                            Toggle::make('status')
                                ->label('Trạng thái kích hoạt')
                                ->required(),
                            Select::make('type')
                                ->label('Khách hàng sử dụng')
                                ->placeholder('Khách hàng sử dụng gói')
                                ->options(fn() => MembershipType::getOptions())
                                ->hidden(!$isSuperAdmin)
                                ->default(MembershipType::FOR_CUSTOMER->value)
                                ->live()
                                ->required(),
                        ]),
                    Section::make('Cấu hình quyền')
                        ->hidden(function (Get $get) use ($isSuperAdmin) {

                            $type = $get('type');
                            return (int) $type != MembershipType::FOR_CUSTOMER->value;
                        })
                        ->schema(function () use ($isSuperAdmin) {
                            return [
                                Toggle::make('config.' . ConfigMembership::ALLOW_COMMENT->value)
                                    ->label(ConfigMembership::ALLOW_COMMENT->labelAdmin()),
                                Toggle::make('config.' . ConfigMembership::ALLOW_CHOOSE_SEAT->value)
                                    ->label(ConfigMembership::ALLOW_CHOOSE_SEAT->labelAdmin()),
                                Toggle::make('config.' . ConfigMembership::ALLOW_DOCUMENTARY->value)
                                    ->label(ConfigMembership::ALLOW_DOCUMENTARY->labelAdmin()),
                            ];
                        })
                ])
            ]);
    }
}
