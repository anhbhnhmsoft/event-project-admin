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
use Illuminate\Support\Facades\Auth;

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
                            Flex::make(
                                [
                                    ColorPicker::make('badge_color_background')
                                        ->label('Màu nền huy hiệu trên trang chủ'),
                                    ColorPicker::make('badge_color_text')
                                        ->label('Màu chữ huy hiệu trên trang chủ'),
                                ]
                            ),
                            Toggle::make('status')
                                ->label('Trạng thái kích hoạt')
                                ->required(),
                            Select::make('type')
                                ->label('Khách hàng sử dụng')
                                ->placeholder('Khách hàng sử dụng gói')
                                ->options(fn() => MembershipType::getOptions())
                                ->hidden(fn(): bool => Auth::user()->role == RoleUser::ADMIN->value)
                                ->required(),
                        ]),
                    Section::make('Cấu hình quyền')
                        ->hidden(fn() => Auth::user()->role === RoleUser::SUPER_ADMIN->value)
                        ->schema(function () {
                            $user = Auth::user();

                            if ($user->role === RoleUser::SUPER_ADMIN->value) {
                                return [
                                    // TextInput::make('config.' . ConfigMembership::LIMIT_EVENT->value)
                                    //     ->label(ConfigMembership::LIMIT_EVENT->labelSuperAdmin())
                                    //     ->required()
                                    //     ->helperText('Giá trị = 99 ~ Không giới hạn')
                                    //     ->minValue(1)
                                    //     ->maxValue(99)
                                    //     ->numeric(),
                                    // TextInput::make('config.' . ConfigMembership::LIMIT_MEMBER->value)
                                    //     ->label(ConfigMembership::LIMIT_MEMBER->labelSuperAdmin())
                                    //     ->required()
                                    //     ->helperText('Giá trị = 99999 ~ Không giới hạn')
                                    //     ->minValue(1)
                                    //     ->maxValue(99999)
                                    //     ->numeric(),
                                    // Toggle::make('config.' . ConfigMembership::FEATURE_POLL->value)
                                    //     ->label(ConfigMembership::FEATURE_POLL->labelSuperAdmin()),
                                    // Toggle::make('config.' . ConfigMembership::FEATURE_GAME->value)
                                    //     ->label(ConfigMembership::FEATURE_GAME->labelSuperAdmin()),
                                    // Toggle::make('config.' . ConfigMembership::FEATURE_COMMENT->value)
                                    //     ->label(ConfigMembership::FEATURE_COMMENT->labelSuperAdmin()),
                                ];
                            } else {
                                return [
                                    Toggle::make('config.' . ConfigMembership::ALLOW_COMMENT->value)
                                        ->label(ConfigMembership::ALLOW_COMMENT->labelAdmin()),
                                    Toggle::make('config.' . ConfigMembership::ALLOW_CHOOSE_SEAT->value)
                                        ->label(ConfigMembership::ALLOW_CHOOSE_SEAT->labelAdmin()),
                                    Toggle::make('config.' . ConfigMembership::ALLOW_DOCUMENTARY->value)
                                        ->label(ConfigMembership::ALLOW_DOCUMENTARY->labelAdmin()),
                                ];
                            }
                        })
                ])
            ]);
    }
}
