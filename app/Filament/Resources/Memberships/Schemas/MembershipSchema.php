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
                Section::make(__('admin.memberships.form.package_info'))
                    ->schema([
                        TextInput::make('name')
                            ->label(__('admin.memberships.form.name'))
                            ->required(),
                        TextInput::make('price')
                            ->label(__('admin.memberships.form.price'))
                            ->minValue(0)
                            ->required()
                            ->helperText(__('admin.memberships.form.price_unit'))
                            ->numeric(),
                        TextInput::make('duration')
                            ->label(__('admin.memberships.form.duration'))
                            ->numeric()
                            ->helperText(__('admin.memberships.form.duration_unit'))
                            ->placeholder(__('admin.memberships.form.duration_placeholder'))
                            ->minValue(0)
                            ->required(),
                        TextInput::make('sort')
                            ->label(__('admin.memberships.form.sort'))
                            ->helperText(__('admin.memberships.form.sort_help'))
                            ->integer()
                            ->minValue(0)
                            ->required(),
                        TextInput::make('product_id')
                            ->label(__('admin.memberships.form.product_id'))
                            ->helperText(__('admin.memberships.form.product_id_help'))
                            ->visible(fn(Get $get) => (int) $get('type') === MembershipType::FOR_CUSTOMER->value),
                        Textarea::make('description')
                            ->required()
                            ->label(__('admin.memberships.form.description'))
                    ]),
                Section::make()->schema([
                    Section::make(__('admin.memberships.form.display_config'))
                        ->schema([
                            TextInput::make('badge')
                                ->label(__('admin.memberships.form.badge'))
                                ->maxLength(255),
                            Flex::make(
                                [
                                    ColorPicker::make('badge_color_background')
                                        ->label(__('admin.memberships.form.badge_bg_color')),
                                    ColorPicker::make('badge_color_text')
                                        ->label(__('admin.memberships.form.badge_text_color')),
                                ]
                            ),
                            Toggle::make('status')
                                ->label(__('admin.memberships.form.status'))
                                ->required(),
                            Select::make('type')
                                ->label(__('admin.memberships.form.customer_type'))
                                ->placeholder(__('admin.memberships.form.customer_type_placeholder'))
                                ->options(fn() => MembershipType::getOptions())
                                ->hidden(!$isSuperAdmin)
                                ->default(MembershipType::FOR_CUSTOMER->value)
                                ->live()
                                ->required(),
                        ]),
                    Section::make(__('admin.memberships.form.permission_config'))
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
