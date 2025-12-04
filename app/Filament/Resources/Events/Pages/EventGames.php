<?php

namespace App\Filament\Resources\Events\Pages;

use App\Filament\Resources\Events\EventResource;
use App\Filament\Traits\CheckPlanBeforeAccess;
use App\Models\Event;
use App\Models\EventGame;
use App\Models\EventGameGift;
use App\Models\User;
use Illuminate\Support\Arr;
use App\Utils\Constants\EventGameType;
use App\Utils\Constants\EventUserHistoryStatus;
use App\Services\EventGameService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Actions\ActionGroup;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;

class EventGames extends Page implements HasTable
{
    use InteractsWithRecord;
    use InteractsWithTable;
    use CheckPlanBeforeAccess;

    // protected static ?string $title = __('event.pages.games_title');
    //     protected static ?string $modelLabel = __('event.pages.games_title');
    //     protected static ?string $pluralModelLabel = __('event.pages.games_title');
    protected static string $resource = EventResource::class;
    protected string $view = "filament.pages.event-games";


    protected static ?string $model = EventGame::class;

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
        $this->ensurePlanAccessible();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(EventGame::query()->where("event_id", $this->record->id))
            ->columns([
                TextColumn::make("name")->label(__('admin.events.games.name')),
                TextColumn::make("description")->label(__('admin.events.games.description')),
                TextColumn::make("game_type")
                    ->label(__('admin.events.games.game_type'))
                    ->formatStateUsing(
                        fn($state) => EventGameType::tryFrom($state)?->label()
                    ),
            ])
            ->headerActions([
                Action::make("create-game-event")
                    ->label(__('admin.events.games.create_new_game'))
                    ->model(EventGame::class)
                    ->schema(
                        [
                            Tabs::make("GameTabs")->tabs([
                                Tab::make(__('admin.events.games.basic_info'))->schema([
                                    TextInput::make("name")
                                        ->label(__('admin.events.games.game_name'))
                                        ->required()
                                        ->validationMessages([
                                            'required' => __('validation.required', ['attribute' => __('admin.events.games.game_name')]),
                                        ]),
                                    Textarea::make("description")
                                        ->label(__('admin.events.games.description'))
                                        ->rows(3),
                                    Select::make("game_type")
                                        ->label(__('admin.events.games.game_type'))
                                        ->options(
                                            \App\Utils\Constants\EventGameType::getOptions()
                                        )
                                        ->required()
                                        ->validationMessages([
                                            'required' => __('validation.required', ['attribute' => __('admin.events.games.game_type')]),
                                        ]),
                                ]),
                                Tab::make(__('admin.events.games.configuration'))->schema([
                                    Repeater::make("gifts")
                                        ->label(__('admin.events.games.gift_pack'))
                                        ->schema([
                                            TextInput::make("name")
                                                ->label(__('admin.events.games.gift_name'))
                                                ->required()
                                                ->validationMessages([
                                                    'required' => __('validation.required', ['attribute' => __('admin.events.games.gift_name')]),
                                                ]),
                                            TextInput::make("quantity")
                                                ->numeric()
                                                ->label(__('admin.events.games.quantity'))
                                                ->required()
                                                ->validationMessages([
                                                    'required' => __('validation.required', ['attribute' => __('admin.events.games.quantity')]),
                                                    'numeric' => __('validation.numeric', ['attribute' => __('admin.events.games.quantity')]),
                                                ]),
                                            Textarea::make("description")
                                                ->label(__('admin.events.games.description')),
                                            FileUpload::make("image")
                                                ->disk("public")
                                                ->directory("event_gifts")
                                                ->label(__('admin.events.games.image'))
                                                ->image()
                                                ->maxSize(51200),
                                        ])
                                        ->collapsible(),
                                    Repeater::make(
                                        "config_game.custom_user_rates"
                                    )
                                        ->label(__('admin.events.games.user_rate_label'))
                                        ->default([])
                                        ->schema([
                                            Repeater::make("rates")
                                                ->label(__('admin.events.games.gift_rate'))
                                                ->schema([
                                                    Select::make("gift_id")
                                                        ->label(__('admin.events.games.gift'))
                                                        ->options(
                                                            fn($record) => EventGameGift::where('event_game_id', $record->id)->pluck(
                                                                "name",
                                                                "id"
                                                            )
                                                        )
                                                        ->required(),
                                                    TextInput::make("rate")
                                                        ->numeric()
                                                        ->minValue(0)
                                                        ->maxValue(100)
                                                        ->suffix("%")
                                                        ->required(),
                                                ])
                                                ->columns(2)
                                                ->collapsible(),
                                        ])
                                        ->columns(1)
                                        ->default([]),
                                ]),
                            ]),
                        ]
                    )
                    ->action(function (array $data) {
                        $game = EventGame::create([
                            ...$data,
                            "event_id" => $this->record->id,
                        ]);

                        $gifts = array_map(function ($gift) use ($game) {
                            $gift["event_game_id"] = $game->id;
                            return $gift;
                        }, $data["gifts"] ?? []);
                        EventGameGift::insert($gifts);

                        Notification::make()
                            ->success()
                            ->title(__('admin.events.games.create_success'))
                            ->body(__('admin.events.games.create_success_message'))
                            ->send();

                        $this->resetTable();
                    }),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()
                        ->label(__('admin.events.games.view'))
                        ->schema(
                            fn($record) => [
                                Tabs::make("GameTabs")->tabs([
                                    Tab::make(__('admin.events.games.basic_info'))->schema([
                                        TextInput::make("name")
                                            ->label(__('admin.events.games.game_name'))
                                            ->disabled(),
                                        Textarea::make("description")
                                            ->label(__('admin.events.games.description'))
                                            ->rows(3)
                                            ->disabled(),
                                        Select::make("game_type")
                                            ->label(__('admin.events.games.game_type'))
                                            ->options(
                                                \App\Utils\Constants\EventGameType::getOptions()
                                            )
                                            ->disabled(),
                                    ]),
                                    Tab::make(__('admin.events.games.configuration'))->schema([
                                        Repeater::make("gifts")
                                            ->relationship("gifts")
                                            ->label(__('admin.events.games.gift_pack'))
                                            ->schema([
                                                TextInput::make("name")
                                                    ->label(__('admin.events.games.gift_name'))
                                                    ->disabled(),
                                                TextInput::make("quantity")
                                                    ->numeric()
                                                    ->label(__('admin.events.games.quantity'))
                                                    ->disabled(),
                                                Textarea::make("description")
                                                    ->label(__('admin.events.games.description'))
                                                    ->disabled(),
                                                FileUpload::make("image")
                                                    ->disk("public")
                                                    ->directory("event_gifts")
                                                    ->label(__('admin.events.games.image'))
                                                    ->image()
                                                    ->maxSize(51200),
                                            ])
                                            ->collapsible(),
                                        Repeater::make(
                                            "config_game.custom_user_rates"
                                        )
                                            ->label(
                                                __('admin.events.games.user_rate_label')
                                            )
                                            ->schema([
                                                Select::make('user_id')
                                                    ->label(__('admin.events.games.player'))
                                                    ->options(function () {
                                                        $event = $this->record;
                                                        if (! $event) {
                                                            return [];
                                                        }
                                                        return $event->users()
                                                            ->wherePivot('status', EventUserHistoryStatus::PARTICIPATED->value)
                                                            ->pluck('users.name', 'users.id')
                                                            ->toArray();
                                                    })
                                                    ->searchable()
                                                    ->required(),
                                                Repeater::make("rates")
                                                    ->label(__('admin.events.games.gift_rate'))
                                                    ->schema([
                                                        Select::make("gift_id")
                                                            ->label(__('admin.events.games.gift'))
                                                            ->options(
                                                                fn($record) => EventGameGift::where('event_game_id', $record->id)->pluck(
                                                                    "name",
                                                                    "id"
                                                                )
                                                            )
                                                            ->required(),
                                                        TextInput::make("rate")
                                                            ->numeric()
                                                            ->minValue(0)
                                                            ->maxValue(100)
                                                            ->suffix("%")
                                                            ->required(),
                                                    ])
                                                    ->columns(2)
                                                    ->collapsible(),
                                            ])
                                            ->columns(1)
                                            ->default([]),
                                    ]),
                                ]),
                            ]
                        )
                        ->fillForm(
                            function ($record) {
                                $record->load('gifts');

                                $data = $record->toArray();
                                $data['config_game'] = $data['config_game'] ?? [];
                                $giftData = $record->gifts->toArray();
                                $data['gifts'] = $giftData;

                                return Arr::only($data, [
                                    "name",
                                    "description",
                                    "game_type",
                                    "config_game",
                                    "gifts",
                                ]);
                            }
                        ),
                    DeleteAction::make()
                        ->recordTitle(__('admin.events.games.game'))
                        ->label(__('admin.events.games.delete'))
                        ->successRedirectUrl(false)
                        ->action(function ($record) {
                            $record->delete();
                        })
                        ->successNotificationTitle(__('admin.events.games.delete_success'))
                        ->after(fn() => $this->resetTable()),
                    Action::make("edit")
                        ->label(__('admin.events.games.edit'))
                        ->icon("heroicon-o-pencil-square")
                        ->schema(
                            fn($record) => [
                                Tabs::make("GameTabs")->tabs([
                                    Tab::make(__('admin.events.games.basic_info'))->schema([
                                        TextInput::make("name")
                                            ->label(__('admin.events.games.game_name'))
                                            ->required()
                                            ->validationMessages([
                                                'required' => __('validation.required', ['attribute' => __('admin.events.games.game_name')]),
                                            ]),
                                        Textarea::make("description")
                                            ->label(__('admin.events.games.description'))
                                            ->rows(3),
                                        Select::make("game_type")
                                            ->label(__('admin.events.games.game_type'))
                                            ->options(
                                                \App\Utils\Constants\EventGameType::getOptions()
                                            )
                                            ->required()
                                            ->validationMessages([
                                                'required' => __('validation.required', ['attribute' => __('admin.events.games.game_type')]),
                                            ]),
                                    ]),
                                    Tab::make(__('admin.events.games.configuration'))->schema([
                                        Repeater::make("gifts")
                                            ->relationship("gifts")
                                            ->label(__('admin.events.games.gift_pack'))
                                            ->schema([
                                                TextInput::make("name")
                                                    ->label(__('admin.events.games.gift_name'))
                                                    ->required()
                                                    ->validationMessages([
                                                        'required' => __('validation.required', ['attribute' => __('admin.events.games.gift_name')]),
                                                    ]),
                                                TextInput::make("quantity")
                                                    ->numeric()
                                                    ->label(__('admin.events.games.quantity'))
                                                    ->required()
                                                    ->validationMessages([
                                                        'required' => __('validation.required', ['attribute' => __('admin.events.games.quantity')]),
                                                        'numeric' => __('validation.numeric', ['attribute' => __('admin.events.games.quantity')]),
                                                    ]),
                                                Textarea::make("description")
                                                    ->label(__('admin.events.games.description')),
                                                FileUpload::make("image")
                                                    ->disk("public")
                                                    ->directory("event_gifts")
                                                    ->label(__('admin.events.games.image'))
                                                    ->image()
                                                    ->maxSize(51200),
                                            ])
                                            ->collapsible(),
                                        Repeater::make(
                                            "config_game.custom_user_rates"
                                        )
                                            ->label(
                                                __('admin.events.games.user_rate_label')
                                            )
                                            ->schema([
                                                Select::make('user_id')
                                                    ->label(__('admin.events.games.player'))
                                                    ->options(function () {
                                                        $event = $this->record;
                                                        if (! $event) {
                                                            return [];
                                                        }
                                                        return $event->users()
                                                            ->wherePivot('status', EventUserHistoryStatus::PARTICIPATED->value)
                                                            ->pluck('users.name', 'users.id')
                                                            ->toArray();
                                                    })
                                                    ->searchable()
                                                    ->required(),
                                                Repeater::make("rates")
                                                    ->label(__('admin.events.games.gift_rate'))
                                                    ->schema([
                                                        Select::make("gift_id")
                                                            ->label(__('admin.events.games.gift'))
                                                            ->options(
                                                                fn($record) => EventGameGift::where('event_game_id', $record->id)->pluck(
                                                                    "name",
                                                                    "id"
                                                                )
                                                            )
                                                            ->required(),
                                                        TextInput::make("rate")
                                                            ->numeric()
                                                            ->minValue(0)
                                                            ->maxValue(100)
                                                            ->suffix("%")
                                                            ->required(),
                                                    ])
                                                    ->columns(2)
                                                    ->collapsible(),
                                            ])
                                            ->columns(1)
                                            ->default([]),
                                    ]),
                                ]),
                            ]
                        )
                        ->fillForm(
                            function ($record) {
                                $record->load('gifts');

                                $data = $record->toArray();
                                $data['config_game'] = $data['config_game'] ?? [];
                                $giftData = $record->gifts->toArray();
                                $data['gifts'] = $giftData;
                                return Arr::only($data, [
                                    "name",
                                    "description",
                                    "game_type",
                                    "config_game",
                                    "gifts",
                                ]);
                            }
                        )
                        ->color("primary")
                        ->action(function ($record, $data) {
                            $eventGameService = app(EventGameService::class);
                            $result = $eventGameService->updateGameEvent($record, $data);
                            if ($result['status']) {
                                Notification::make()
                                    ->success()
                                    ->title(__('admin.events.games.update_success'))
                                    ->send();
                            } else {
                                Notification::make()
                                    ->danger()
                                    ->title(__('admin.events.games.update_failed'))
                                    ->send();
                            }

                            $this->resetTable();
                        }),
                    Action::make("link")
                        ->label(__('admin.events.games.open_page'))
                        ->icon("heroicon-o-link")
                        ->color("primary")
                        ->url(
                            fn($record) => route("game.play", [
                                "id" => $record->id,
                            ])
                        )
                        ->openUrlInNewTab()
                        ->extraAttributes(["class" => "copy-url-action"]),
                ]),
            ]);
    }
}
