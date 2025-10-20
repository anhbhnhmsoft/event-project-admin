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
                TextColumn::make("name")->label("Tên"),
                TextColumn::make("description")->label("Miêu tả"),
                TextColumn::make("game_type")
                    ->label("Loại trò chơi")
                    ->formatStateUsing(
                        fn($state) => EventGameType::tryFrom($state)?->label()
                    ),
            ])
            ->headerActions([
                Action::make("create-game-event")
                    ->label("Tạo trò chơi mới")
                    ->model(EventGame::class)
                    ->schema(
                        [
                            Tabs::make("GameTabs")->tabs([
                                Tab::make("Thông tin cơ bản")->schema([
                                    TextInput::make("name")
                                        ->label("Tên trò chơi")
                                        ->required(),
                                    Textarea::make("description")
                                        ->label("Miêu tả")
                                        ->rows(3),
                                    Select::make("game_type")
                                        ->label("Loại trò chơi")
                                        ->options(
                                            \App\Utils\Constants\EventGameType::getOptions()
                                        )
                                        ->required(),
                                ]),
                                Tab::make("Cấu hình")->schema([
                                    Repeater::make("gifts")
                                        ->label("Gói quà")
                                        ->schema([
                                            TextInput::make("name")
                                                ->label("Tên gói quà")
                                                ->required(),
                                            TextInput::make("quantity")
                                                ->numeric()
                                                ->label("Số lượng")
                                                ->required(),
                                            Textarea::make("description")
                                                ->label("Miêu tả"),
                                            FileUpload::make("image")
                                                ->disk("public")
                                                ->directory("event_gifts")
                                                ->label("Hình ảnh")
                                                ->image()
                                                ->maxSize(51200),
                                        ])
                                        ->collapsible(),
                                    Repeater::make(
                                        "config_game.custom_user_rates"
                                    )
                                        ->label("Tỉ lệ chỉ định cho người chơi")
                                        ->default([])
                                        ->schema([
                                            Select::make("user_id")
                                                ->label("Người chơi")
                                                ->options(
                                                    function ($record) {
                                                        $event = Event::find($record->event_id);
                                                        return $event->users()->wherePivot('status', EventUserHistoryStatus::PARTICIPATED->value)->select('users.id', 'users.name')->get();
                                                    }
                                                )
                                                ->searchable()
                                                ->required(),
                                            Repeater::make("rates")
                                                ->label("Tỉ lệ quà")
                                                ->schema([
                                                    Select::make("gift_id")
                                                        ->label("Phần quà")
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
                            ->title("Tạo thành công")
                            ->body("Trò chơi mới đã được thêm vào sự kiện.")
                            ->send();

                        $this->resetTable();
                    }),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()
                        ->label("Xem")
                        ->schema(
                            fn($record) => [
                                Tabs::make("GameTabs")->tabs([
                                    Tab::make("Thông tin cơ bản")->schema([
                                        TextInput::make("name")
                                            ->label("Tên trò chơi")
                                            ->disabled(),
                                        Textarea::make("description")
                                            ->label("Miêu tả")
                                            ->rows(3)
                                            ->disabled(),
                                        Select::make("game_type")
                                            ->label("Loại trò chơi")
                                            ->options(
                                                \App\Utils\Constants\EventGameType::getOptions()
                                            )
                                            ->disabled(),
                                    ]),
                                    Tab::make("Cấu hình")->schema([
                                        Repeater::make("gifts")
                                            ->relationship("gifts")
                                            ->label("Gói quà")
                                            ->schema([
                                                TextInput::make("name")
                                                    ->label("Tên gói quà")
                                                    ->disabled(),
                                                TextInput::make("quantity")
                                                    ->numeric()
                                                    ->label("Số lượng")
                                                    ->disabled(),
                                                Textarea::make("description")
                                                    ->label("Miêu tả")
                                                    ->disabled(),
                                                FileUpload::make("image")
                                                    ->disk("public")
                                                    ->directory("event_gifts")
                                                    ->label("Hình ảnh")
                                                    ->image()
                                                    ->maxSize(51200),
                                            ])
                                            ->collapsible(),
                                        Repeater::make(
                                            "config_game.custom_user_rates"
                                        )
                                            ->label(
                                                "Tỉ lệ chỉ định cho người chơi"
                                            )
                                            ->schema([
                                                Select::make("user_id")
                                                    ->label("Người chơi")
                                                    ->options(
                                                        function ($record) {
                                                            $event = Event::find($record->event_id);
                                                            return $event->users()->wherePivot('status', EventUserHistoryStatus::PARTICIPATED->value)->select('users.id', 'users.name')->get()->pluck('name', 'id')->toArray();
                                                        }
                                                    )
                                                    ->searchable()
                                                    ->required(),
                                                Repeater::make("rates")
                                                    ->label("Tỉ lệ quà")
                                                    ->schema([
                                                        Select::make("gift_id")
                                                            ->label("Phần quà")
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
                        ->recordTitle("trò chơi")
                        ->label("Xóa")
                        ->successRedirectUrl(false)
                        ->action(function ($record) {
                            $record->delete();
                        })
                        ->successNotificationTitle("Đã xóa trò chơi!")
                        ->after(fn() => $this->resetTable()),
                    Action::make("edit")
                        ->label("Sửa")
                        ->icon("heroicon-o-pencil-square")
                        ->schema(
                            fn($record) => [
                                Tabs::make("GameTabs")->tabs([
                                    Tab::make("Thông tin cơ bản")->schema([
                                        TextInput::make("name")
                                            ->label("Tên trò chơi")
                                            ->required(),
                                        Textarea::make("description")
                                            ->label("Miêu tả")
                                            ->rows(3),
                                        Select::make("game_type")
                                            ->label("Loại trò chơi")
                                            ->options(
                                                \App\Utils\Constants\EventGameType::getOptions()
                                            )
                                            ->required(),
                                    ]),
                                    Tab::make("Cấu hình")->schema([
                                        Repeater::make("gifts")
                                            ->relationship("gifts")
                                            ->label("Gói quà")
                                            ->schema([
                                                TextInput::make("name")
                                                    ->label("Tên gói quà")
                                                    ->required(),
                                                TextInput::make("quantity")
                                                    ->numeric()
                                                    ->label("Số lượng")
                                                    ->required(),
                                                Textarea::make("description")
                                                    ->label("Miêu tả"),
                                                FileUpload::make("image")
                                                    ->disk("public")
                                                    ->directory("event_gifts")
                                                    ->label("Hình ảnh")
                                                    ->image()
                                                    ->maxSize(51200),
                                            ])
                                            ->collapsible(),
                                        Repeater::make(
                                            "config_game.custom_user_rates"
                                        )
                                            ->label(
                                                "Tỉ lệ chỉ định cho người chơi"
                                            )
                                            ->schema([
                                                Select::make("user_id")
                                                    ->label("Người chơi")
                                                    ->options(
                                                        function ($record) {
                                                            $event = Event::find($record->event_id);
                                                            return $event->users()->wherePivot('status', EventUserHistoryStatus::PARTICIPATED->value)->select('users.id', 'users.name')->get()->pluck('name', 'id')->toArray();
                                                        }
                                                    )
                                                    ->searchable()
                                                    ->required(),
                                                Repeater::make("rates")
                                                    ->label("Tỉ lệ quà")
                                                    ->schema([
                                                        Select::make("gift_id")
                                                            ->label("Phần quà")
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
                                    ->title("Cập nhật thành công")
                                    ->send();
                            } else {
                                Notification::make()
                                    ->danger()
                                    ->title("Cập nhật thất bại")
                                    ->send();
                            }

                            $this->resetTable();
                        }),
                    Action::make("link")
                        ->label("Mở trang")
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
