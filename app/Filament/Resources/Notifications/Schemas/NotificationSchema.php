<?php

namespace App\Filament\Resources\Notifications\Schemas;

use App\Models\User;
use App\Models\Organizer;
use App\Utils\Constants\RoleUser;
use App\Utils\Constants\TypeSendNotification;
use App\Utils\Constants\UserNotificationType;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
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

        if ($user->role == RoleUser::SUPER_ADMIN->value) {
            $steps[] = Step::make(__('admin.notifications.form.steps.select_organizer'))
                ->schema([
                    Select::make('organizer_id')
                        ->label(__('admin.notifications.form.organizer'))
                        ->options(fn() => Organizer::query()->orderBy('name')->pluck('name', 'id'))
                        ->searchable()
                        ->preload()
                        ->required()
                        ->live()
                        ->validationMessages([
                            'required' => __('admin.notifications.form.validation.organizer_required'),
                        ]),
                ]);
        } else {
            $steps[] = Step::make(__('admin.notifications.form.steps.recipients_content'))
                ->schema([
                    Hidden::make('organizer_id')
                        ->default(fn() => $user->organizer_id ?? null)
                        ->dehydrated(),
                ]);
        }

        $steps[] = Step::make(__('admin.notifications.form.steps.recipients_content'))
            ->schema([
                Select::make('template_id')
                    ->label(__('admin.notifications.form.use_template'))
                    ->placeholder(__('admin.notifications.form.use_template_placeholder'))
                    ->options(function (Get $get) use ($user) {
                        $organizerId = $get('organizer_id') ?: ($user->organizer_id ?? null);
                        return \App\Models\NotificationTemplate::query()
                            ->when($organizerId, fn($q) => $q->where('organizer_id', $organizerId))
                            ->active()
                            ->orderBy('created_at', 'desc')
                            ->pluck('name', 'id');
                    })
                    ->searchable()
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state) {
                            $template = \App\Models\NotificationTemplate::find($state);
                            if ($template) {
                                $set('notification_type', $template->notification_type);
                                $set('title', $template->title);
                                $set('description', $template->description);
                            }
                        }
                    })
                    ->dehydrated(false)
                    ->columnSpanFull(),
                Radio::make('mode')
                    ->label(__('admin.notifications.form.send_mode'))
                    ->options(TypeSendNotification::getOptions())
                    ->default(TypeSendNotification::SOME_USERS->value)
                    ->inline()
                    ->live()
                    ->validationMessages([
                        'required' => __('admin.notifications.form.validation.send_mode_required'),
                    ]),
                Select::make('user_ids')
                    ->label(__('admin.notifications.form.recipients'))
                    ->options(function (Get $get) use ($user) {
                        $organizerId = $get('organizer_id') ?: ($user->organizer_id ?? null);
                        return User::query()
                            ->when($organizerId, fn(Builder $q) => $q->where('organizer_id', $organizerId))
                            ->orderBy('email')
                            ->pluck('email', 'id');
                    })
                    ->searchable()
                    ->preload()
                    ->multiple()
                    ->visible(fn(Get $get) => $get('mode') == TypeSendNotification::SOME_USERS->value)
                    ->required(fn(Get $get) => $get('mode') == TypeSendNotification::SOME_USERS->value)
                    ->validationMessages([
                        'exists' => __('admin.notifications.form.validation.user_not_exists'),
                        'required' => __('admin.notifications.form.validation.recipients_required'),
                    ]),
                Select::make('notification_type')
                    ->label(__('admin.notifications.form.notification_type'))
                    ->options(UserNotificationType::getOptions())
                    ->required()
                    ->validationMessages([
                        'required' => __('admin.notifications.form.validation.notification_type_required'),
                    ]),
                TextInput::make('title')
                    ->label(__('admin.notifications.form.title'))
                    ->required()
                    ->maxLength(255)
                    ->validationMessages([
                        'required' => __('admin.notifications.form.validation.title_required'),
                    ]),
                Textarea::make('description')
                    ->label(__('admin.notifications.form.description'))
                    ->required()
                    ->columnSpanFull()
                    ->validationMessages([
                        'required' => __('admin.notifications.form.validation.description_required'),
                    ])
                    ->extraAttributes(['style' => 'min-height: 300px;']),
                \Filament\Forms\Components\Checkbox::make('save_as_template')
                    ->label(__('admin.notifications.form.save_as_template'))
                    ->default(false)
                    ->live()
                    ->columnSpanFull(),
                \Filament\Forms\Components\TextInput::make('template_name')
                    ->label(__('admin.notifications.form.template_name'))
                    ->visible(fn(Get $get) => $get('save_as_template') === true)
                    ->required(fn(Get $get) => $get('save_as_template') === true)
                    ->maxLength(255)
                    ->placeholder(__('admin.notifications.form.template_name_placeholder'))
                    ->columnSpanFull()
                    ->validationMessages([
                        'required' => __('admin.notifications.form.validation.template_name_required'),
                    ]),
            ]);

        if ($user->role == RoleUser::SUPER_ADMIN->value) {
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
                    ->default(fn() => $user->organizer_id ?? null)
                    ->dehydrated(),
                Select::make('template_id')
                    ->label(__('admin.notifications.form.use_template'))
                    ->placeholder(__('admin.notifications.form.use_template_placeholder'))
                    ->options(function (Get $get) use ($user) {
                        $organizerId = $get('organizer_id') ?: ($user->organizer_id ?? null);
                        return \App\Models\NotificationTemplate::query()
                            ->when($organizerId, fn($q) => $q->where('organizer_id', $organizerId))
                            ->active()
                            ->orderBy('created_at', 'desc')
                            ->pluck('name', 'id');
                    })
                    ->searchable()
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state) {
                            $template = \App\Models\NotificationTemplate::find($state);
                            if ($template) {
                                $set('notification_type', $template->notification_type);
                                $set('title', $template->title);
                                $set('description', $template->description);
                            }
                        }
                    })
                    ->dehydrated(false)
                    ->columnSpanFull(),
                Radio::make('mode')
                    ->label(__('admin.notifications.form.send_mode'))
                    ->options(TypeSendNotification::getOptions())
                    ->default(TypeSendNotification::SOME_USERS->value)
                    ->inline()
                    ->live()
                    ->validationMessages([
                        'required' => __('admin.notifications.form.validation.send_mode_required'),
                    ]),
                Select::make('user_ids')
                    ->label(__('admin.notifications.form.recipients'))
                    ->options(function (Get $get) use ($user) {
                        $organizerId = $get('organizer_id') ?: ($user->organizer_id ?? null);
                        return User::query()
                            ->when($organizerId, fn(Builder $q) => $q->where('organizer_id', $organizerId))
                            ->orderBy('email')
                            ->pluck('email', 'id');
                    })
                    ->searchable()
                    ->preload()
                    ->multiple()
                    ->visible(fn(Get $get) => $get('mode') == TypeSendNotification::SOME_USERS->value)
                    ->required(fn(Get $get) => $get('mode') == TypeSendNotification::SOME_USERS->value)
                    ->validationMessages([
                        'exists' => __('admin.notifications.form.validation.user_not_exists'),
                        'required' => __('admin.notifications.form.validation.recipients_required'),
                    ]),
                Select::make('notification_type')
                    ->label(__('admin.notifications.form.notification_type'))
                    ->options(UserNotificationType::getOptions())
                    ->required()
                    ->validationMessages([
                        'required' => __('admin.notifications.form.validation.notification_type_required'),
                    ]),
                TextInput::make('title')
                    ->label(__('admin.notifications.form.title'))
                    ->required()
                    ->maxLength(255)
                    ->validationMessages([
                        'required' => __('admin.notifications.form.validation.title_required'),
                    ]),
                Textarea::make('description')
                    ->label(__('admin.notifications.form.description'))
                    ->required()
                    ->columnSpanFull()
                    ->validationMessages([
                        'required' => __('admin.notifications.form.validation.description_required'),
                    ])
                    ->extraAttributes(['style' => 'min-height: 300px;']),
                \Filament\Forms\Components\Checkbox::make('save_as_template')
                    ->label(__('admin.notifications.form.save_as_template'))
                    ->default(false)
                    ->live()
                    ->columnSpanFull(),
                \Filament\Forms\Components\TextInput::make('template_name')
                    ->label(__('admin.notifications.form.template_name'))
                    ->visible(fn(Get $get) => $get('save_as_template') === true)
                    ->required(fn(Get $get) => $get('save_as_template') === true)
                    ->maxLength(255)
                    ->placeholder(__('admin.notifications.form.template_name_placeholder'))
                    ->columnSpanFull()
                    ->validationMessages([
                        'required' => __('admin.notifications.form.validation.template_name_required'),
                    ]),
            ])
            ->columns(null)
            ->statePath('data');
    }
}
