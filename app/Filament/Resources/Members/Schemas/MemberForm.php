<?php

namespace App\Filament\Resources\Members\Schemas;

use App\Models\Membership;
use App\Utils\Constants\MembershipUserStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MemberForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->components([
                        Grid::make()
                            ->columns(2)
                            ->components([
                                Section::make(__('admin.members.columns.info_user'))
                                    ->components([
                                        TextInput::make('name')
                                            ->label(__('admin.members.columns.user_name'))
                                            ->required(),
                                        FileUpload::make('avatar_path')
                                            ->label(__('admin.users.form.avatar'))
                                            ->image()
                                            ->imageEditor()
                                            ->disk('public')
                                            ->directory('avatars')
                                            ->visibility('public')
                                            ->nullable()
                                            ->columnSpanFull(),
                                        TextInput::make('email')
                                            ->label(__('admin.members.columns.email'))
                                            ->required(),
                                        TextInput::make('phone')
                                            ->label(__('admin.members.columns.phone'))
                                            ->required(),
                                        TextInput::make('address')
                                            ->label(__('admin.members.columns.address'))
                                            ->required(),
                                        TextInput::make('introduce')
                                            ->label(__('admin.members.columns.introduce'))
                                            ->required(),
                                    ]),
                                Section::make(__('admin.members.columns.info_package_registed'))
                                    ->components([
                                        Repeater::make('memberships')
                                            ->label(__('admin.members.columns.memberships'))
                                            ->columns(2)
                                            ->relationship('memberships')
                                            ->schema([
                                                TextInput::make('name')
                                                    ->label(__('admin.members.columns.package'))
                                                    ->required(),
                                                DatePicker::make('start_date')
                                                    ->label(__('admin.members.columns.start_date'))
                                                    ->required(),

                                                DatePicker::make('end_date')
                                                    ->label(__('admin.members.columns.end_date'))
                                                    ->required(),

                                                Select::make('status')
                                                    ->label(__('admin.members.columns.status'))
                                                    ->required()
                                                    ->options(MembershipUserStatus::toOptions()),
                                            ]),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull(),

            ]);
    }
}
