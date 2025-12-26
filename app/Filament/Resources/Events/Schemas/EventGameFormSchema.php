<?php

namespace App\Filament\Resources\Events\Schemas;

use App\Models\Event;
use App\Models\EventGameGift;
use App\Utils\Constants\EventGameType;
use App\Utils\Constants\EventUserHistoryStatus;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;

class EventGameFormSchema
{
    /**
     * Generate basic info schema (name, description, game_type)
     */
    public static function basicInfoSchema(bool $isDisabled = false): array
    {
        return [
            TextInput::make('name')
                ->label(__('admin.events.games.game_name'))
                ->required(!$isDisabled)
                ->disabled($isDisabled)
                ->validationMessages([
                    'required' => __('validation.required', ['attribute' => __('admin.events.games.game_name')]),
                ]),
            Textarea::make('description')
                ->label(__('admin.events.games.description'))
                ->rows(3)
                ->disabled($isDisabled),
            Select::make('game_type')
                ->label(__('admin.events.games.game_type'))
                ->options(EventGameType::getOptions())
                ->required(!$isDisabled)
                ->disabled($isDisabled)
                ->validationMessages([
                    'required' => __('validation.required', ['attribute' => __('admin.events.games.game_type')]),
                ]),
        ];
    }

    /**
     * Generate gifts schema
     */
    public static function giftsSchema(bool $isDisabled = false, $record = null): array
    {
        $schema = [
            TextInput::make('name')
                ->label(__('admin.events.games.gift_name'))
                ->required(!$isDisabled)
                ->disabled($isDisabled)
                ->validationMessages([
                    'required' => __('validation.required', ['attribute' => __('admin.events.games.gift_name')]),
                ]),
            TextInput::make('quantity')
                ->numeric()
                ->label(__('admin.events.games.quantity'))
                ->required(!$isDisabled)
                ->disabled($isDisabled)
                ->validationMessages([
                    'required' => __('validation.required', ['attribute' => __('admin.events.games.quantity')]),
                    'numeric' => __('validation.numeric', ['attribute' => __('admin.events.games.quantity')]),
                ]),
            Textarea::make('description')
                ->label(__('admin.events.games.description'))
                ->disabled($isDisabled),
            FileUpload::make('image')
                ->disk('public')
                ->directory('event_gifts')
                ->label(__('admin.events.games.image'))
                ->image()
                ->maxSize(51200)
                ->disabled($isDisabled),
        ];

        return $schema;
    }

    /**
     * Generate custom user rates schema
     */
    public static function customRatesSchema(?Event $event, bool $isDisabled = false, $record = null): array
    {
        return [
            Select::make('user_id')
                ->label(__('admin.events.games.player'))
                ->options(function () use ($event) {
                    if (!$event) {
                        return [];
                    }
                    return $event->users()
                        ->wherePivot('status', EventUserHistoryStatus::PARTICIPATED->value)
                        ->pluck('users.name', 'users.id')
                        ->toArray();
                })
                ->searchable()
                ->required(!$isDisabled)
                ->disabled($isDisabled),
            Repeater::make('rates')
                ->label(__('admin.events.games.gift_rate'))
                ->schema([
                    Select::make('gift_id')
                        ->label(__('admin.events.games.gift'))
                        ->options(
                            fn($record) => EventGameGift::where('event_game_id', $record?->id)->pluck('name', 'id')
                        )
                        ->required(!$isDisabled)
                        ->disabled($isDisabled),
                    TextInput::make('rate')
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(100)
                        ->suffix('%')
                        ->required(!$isDisabled)
                        ->disabled($isDisabled),
                ])
                ->columns(2)
                ->collapsible()
                ->disabled($isDisabled),
        ];
    }

    /**
     * Generate complete form schema based on mode
     * 
     * @param Event|null $event The event record
     * @param string $mode 'create', 'view', or 'edit'
     * @return array
     */
    public static function make(?Event $event, string $mode = 'create'): array
    {
        $isDisabled = ($mode === 'view');
        $record = null; // Will be bound by Filament context

        return [
            Tabs::make('GameTabs')->tabs([
                Tab::make(__('admin.events.games.basic_info'))
                    ->schema(static::basicInfoSchema($isDisabled)),

                Tab::make(__('admin.events.games.configuration'))
                    ->schema([
                        Repeater::make('gifts')
                            ->when($mode !== 'create', fn($repeater) => $repeater->relationship('gifts'))
                            ->label(__('admin.events.games.gift_pack'))
                            ->schema(static::giftsSchema($isDisabled, $record))
                            ->collapsible(),

                        Repeater::make('config_game.custom_user_rates')
                            ->label(__('admin.events.games.user_rate_label'))
                            ->schema(static::customRatesSchema($event, $isDisabled, $record))
                            ->columns(1)
                            ->default([]),
                    ]),
            ]),
        ];
    }
}
