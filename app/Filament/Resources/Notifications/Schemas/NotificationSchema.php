<?php

namespace App\Filament\Resources\Notifications\Schemas;

use App\Models\User;
use App\Models\Organizer;
use App\Utils\Constants\RoleUser;
use App\Utils\Constants\UserNotificationType;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class NotificationSchema
{
    public static function make(Schema $schema): Schema
    {
        $user = Auth::user();
        $steps = [];

        if ($user->role === RoleUser::SUPER_ADMIN->value) {
            $steps[] = Step::make('Chọn nhà tổ chức')
                ->schema([
                    Select::make('organizer_id')
                        ->label('Nhà tổ chức')
                        ->options(fn () => Organizer::query()->orderBy('name')->pluck('name', 'id'))
                        ->searchable()
                        ->preload()
                        ->required()
                        ->live()
                        ->validationMessages([
                            'required' => 'Vui lòng chọn nhà tổ chức.',
                        ]),
                ]);
        } else {
            $steps[] = Step::make('Người nhận & nội dung')
                ->schema([
                    Hidden::make('organizer_id')
                        ->default(fn () => $user->organizer_id ?? null)
                        ->dehydrated(),
                ]);
        }

        $steps[] = Step::make('Người nhận & nội dung')
            ->schema([
                Radio::make('mode')
                    ->label('Chế độ gửi')
                    ->options([
                        'single' => 'Chọn người dùng',
                        'broadcast' => 'Broadcast (toàn bộ người dùng)',
                    ])
                    ->default('single')
                    ->inline()
                    ->live()
                    ->validationMessages([
                        'required' => 'Vui lòng chọn chế độ gửi.',
                    ]),
                Select::make('user_ids')
                    ->label('Người nhận')
                    ->options(function (Get $get) use ($user) {
                        $organizerId = $get('organizer_id') ?: ($user->organizer_id ?? null);
                        return User::query()
                            ->when($organizerId, fn (Builder $q) => $q->where('organizer_id', $organizerId))
                            ->orderBy('email')
                            ->pluck('email', 'id');
                    })
                    ->searchable()
                    ->preload()
                    ->multiple()
                    ->visible(fn (Get $get) => $get('mode') === 'single')
                    ->required(fn (Get $get) => $get('mode') === 'single')
                    ->validationMessages([
                        'exists' => 'Người dùng không tồn tại trong nhà tổ chức này.',
                        'required' => 'Vui lòng chọn người nhận.',
                    ]),
                Select::make('notification_type')
                    ->label('Loại thông báo')
                    ->options(UserNotificationType::getOptions())
                    ->required()
                    ->validationMessages([
                        'required' => 'Vui lòng chọn loại thông báo.',
                    ]),
                TextInput::make('title')
                    ->label('Tiêu đề')
                    ->required()
                    ->maxLength(255)
                    ->validationMessages([
                        'required' => 'Vui lòng nhập tiêu đề.',
                    ]),
                RichEditor::make('description')
                    ->label('Mô tả')
                    ->required()
                    ->columnSpanFull()
                    ->validationMessages([
                        'required' => 'Vui lòng nhập Mô tả',
                    ])
                    ->extraAttributes(['style' => 'min-height: 300px;']),
            ]);

        if ($user->role === RoleUser::SUPER_ADMIN->value) {
            return $schema
                ->components([
                    Wizard::make($steps),
                ])
                ->columns(null)
                ->statePath('data');
        }

        return $schema
            ->components([
                Hidden::make('organizer_id')
                    ->default(fn () => $user->organizer_id ?? null)
                    ->dehydrated(),
                Radio::make('mode')
                    ->label('Chế độ gửi')
                    ->options([
                        'single' => 'Chọn người dùng',
                        'broadcast' => 'Broadcast (toàn bộ người dùng)',
                    ])
                    ->default('single')
                    ->inline()
                    ->live()
                    ->validationMessages([
                        'required' => 'Vui lòng chọn chế độ gửi.',
                    ]),
                Select::make('user_ids')
                    ->label('Người nhận')
                    ->options(function (Get $get) use ($user) {
                        $organizerId = $get('organizer_id') ?: ($user->organizer_id ?? null);
                        return User::query()
                            ->when($organizerId, fn (Builder $q) => $q->where('organizer_id', $organizerId))
                            ->orderBy('email')
                            ->pluck('email', 'id');
                    })
                    ->searchable()
                    ->preload()
                    ->multiple()
                    ->visible(fn (Get $get) => $get('mode') === 'single')
                    ->required(fn (Get $get) => $get('mode') === 'single')
                    ->validationMessages([
                        'exists' => 'Người dùng không tồn tại trong nhà tổ chức này.',
                        'required' => 'Vui lòng chọn người nhận.',
                    ]),
                Select::make('notification_type')
                    ->label('Loại thông báo')
                    ->options(UserNotificationType::getOptions())
                    ->required()
                    ->validationMessages([
                        'required' => 'Vui lòng chọn loại thông báo.',
                    ]),
                TextInput::make('title')
                    ->label('Tiêu đề')
                    ->required()
                    ->maxLength(255)
                    ->validationMessages([
                        'required' => 'Vui lòng nhập tiêu đề.',
                    ]),
                RichEditor::make('description')
                    ->label('Mô tả')
                    ->required()
                    ->columnSpanFull()
                    ->validationMessages([
                        'required' => 'Vui lòng nhập Mô tả',
                    ])
                    ->extraAttributes(['style' => 'min-height: 300px;']),
            ])
            ->columns(null)
            ->statePath('data');
    }
}