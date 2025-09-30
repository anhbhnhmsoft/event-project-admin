<?php

namespace App\Filament\Resources\Notifications\Schemas;

use App\Models\User;
use App\Models\Organizer;
use App\Models\Event;
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

        return $schema
            ->components([
                Wizard::make([
                    Step::make('Chọn nhà tổ chức & sự kiện')
                        ->schema([
                            Hidden::make('organizer_id')
                                ->default(fn () => $user->organizer_id ?? null)
                                ->dehydrated()
                                ->visible(fn () => $user && ((int) ($user->role ?? 0)) !== RoleUser::SUPER_ADMIN->value),
                            Select::make('organizer_id')
                                ->label('Nhà tổ chức')
                                ->options(fn () => Organizer::query()->orderBy('name')->pluck('name', 'id'))
                                ->searchable()
                                ->preload()
                                ->required()
                                ->live()
                                ->visible(fn () => $user && ((int) ($user->role ?? 0)) === RoleUser::SUPER_ADMIN->value)
                                ->validationMessages([
                                    'required' => 'Vui lòng chọn nhà tổ chức.',
                                ]),
                            Select::make('event_id')
                                ->label('Sự kiện')
                                ->options(function (Get $get) {
                                    $organizerId = $get('organizer_id') ?: ($user->organizer_id ?? null);
                                    return Event::query()
                                        ->when($organizerId, fn ($q) => $q->where('organizer_id', $organizerId))
                                        ->orderBy('name')
                                        ->pluck('name', 'id');
                                })
                                ->searchable()
                                ->required()
                                ->preload()
                                ->live()
                                ->validationMessages([
                                    'exists' => 'Sự kiện không tồn tại trong nhà tổ chức này.',
                                    'required' => 'Vui lòng chọn sự kiện.',
                                ]),
                        ]),
                    Step::make('Người nhận & nội dung')
                        ->schema([
                            Radio::make('mode')
                                ->label('Chế độ gửi')
                                ->options([
                                    'single' => 'Chọn người dùng',
                                    'broadcast' => 'Broadcast (toàn bộ organizer)',
                                ])
                                ->default('single')
                                ->inline()
                                ->live()
                                ->validationMessages([
                                    'required' => 'Vui lòng chọn chế độ gửi.',
                                ]),
                            Select::make('user_ids')
                                ->label('Người nhận')
                                ->options(function (Get $get) {
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
                        ]),
                ]),
            ])->columns(null)
            ->statePath('data');
    }
}