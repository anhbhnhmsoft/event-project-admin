<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;
use App\Utils\Constants\RoleUser;
use App\Utils\Constants\Language;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Fieldset;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Tên người dùng')
                    ->required(),
                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required(),
                TextInput::make('phone')
                    ->label('Số điện thoại')
                    ->tel(),
                TextInput::make('address')
                    ->label('Địa chỉ'),
                Textarea::make('introduce')
                    ->label('Giới thiệu')
                    ->columnSpanFull(),
                Select::make('role')
                    ->label('Vai trò')
                    ->options(RoleUser::getOptions())
                    ->required(),
                Select::make('lang')
                    ->label('Ngôn ngữ')
                    ->options(Language::getOptions())
                    ->default('vi'),
                FileUpload::make('avatar_path')
                    ->label('Ảnh đại diện')
                    ->image()
                    ->imageEditor()
                    ->disk('public')
                    ->directory('avatars')
                    ->visibility('public')
                    ->nullable()
                    ->columnSpanFull(),
                Fieldset::make('Password')
                    ->label('Mật khẩu')
                    ->schema([
                        TextInput::make('password')
                            ->label('Mật khẩu hiện tại')
                            ->readOnly()
                            ->columnSpanFull()
                            ->placeholder('●●●●●●●●●●●●●●●●●●●●●●●●●●●●●●●●●●●●')
                            ->disabled(fn($get, $context) => $get('showChangePassword') !== true || $context === 'create')
                            ->default(fn($record) => $record?->password ?? '')
                            ->visible(fn($get, $record) => $record !== null && $get('showChangePassword') !== true)
                            ->suffixAction(
                                Action::make('changePassword')
                                    ->label('Thay đổi mật khẩu')
                                    ->icon('heroicon-o-pencil')
                                    ->action(function ($get, $set) {
                                        $set('showChangePassword', true);
                                    })
                            ),
                        TextInput::make('new_password')
                            ->label('Mật khẩu mới')
                            ->password()
                            ->visible(fn($get, $record) => $record === null || $get('showChangePassword') === true)
                            ->required(fn($record) => $record === null)
                            ->dehydrateStateUsing(fn($state) => !empty($state) ? bcrypt($state) : null)
                            ->dehydrated(fn($state) => filled($state))
                            ->maxLength(255),
                        TextInput::make('new_password_confirmation')
                            ->label('Xác nhận mật khẩu mới')
                            ->password()
                            ->visible(fn($get, $record) => $record === null || $get('showChangePassword') === true)
                            ->same('new_password')
                            ->required(fn($record) => $record === null),
                        Hidden::make('showChangePassword')->default(false),
                    ])
                    ->columnSpanFull(),
                DateTimePicker::make('email_verified_at')
                    ->label('Ngày xác thực email'),
                DateTimePicker::make('phone_verified_at')
                    ->label('Ngày xác thực số điện thoại'),
            ]);
    }
}
