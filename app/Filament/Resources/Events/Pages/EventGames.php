<?php

namespace App\Filament\Resources\Events\Pages;

use App\Filament\Resources\Events\EventResource;
use App\Models\EventGame;
use App\Models\EventGameGift;
use App\Models\User;
use App\Utils\Constants\ConfigGameEvent;
use App\Utils\Constants\EventGameType;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Section;
use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Actions\ActionGroup;
use Filament\Tables\Columns\IconColumn;
use Illuminate\Support\Facades\Vite;

class EventGames extends Page implements HasTable
{
    use InteractsWithRecord;
    use InteractsWithTable;

    protected static ?string $title = 'Trò chơi';
    protected static ?string $modelLabel = 'Trò chơi';
    protected static ?string $pluralModelLabel = 'Trò chơi';
    protected static string $resource = EventResource::class;
    protected string $view = 'filament.pages.event-games';

    public function boot()
    {
        FilamentAsset::register([
            Css::make('app-css', Vite::asset('resources/css/app.css')),
        ]);
    }

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                EventGame::query()->where('event_id', $this->record->id)
            )
            ->columns([
                TextColumn::make('name')
                    ->label('Tên'),
                TextColumn::make('description')
                    ->label('Miêu tả'),
                TextColumn::make('game_type')
                    ->label('Loại trò chơi')
                    ->formatStateUsing(fn($state) => EventGameType::tryFrom($state)?->label()),
            ])
            ->headerActions([
                Action::make('create-game-event')
                    ->label('Tạo trò chơi mới')
                    ->model(EventGame::class)
                    ->schema([
                        Flex::make([
                            Section::make('Thông tin cơ bản')
                                ->schema([
                                    TextInput::make('name')
                                        ->label('Tên trò chơi')
                                        ->required()
                                        ->maxLength(255),
                                    Textarea::make('description')
                                        ->label('Miêu tả')
                                        ->rows(3),
                                    Select::make('game_type')
                                        ->label('Loại trò chơi')
                                        ->options(
                                            \App\Utils\Constants\EventGameType::getOptions()
                                        )
                                        ->required(),
                                ]),
                            Section::make('Cấu hình')
                                ->schema([
                                    Repeater::make('gifts')
                                        ->label('Gói quà')
                                        ->schema([
                                            TextInput::make('name')->label('Tên gói quà')->required(),
                                            TextInput::make('quantity')->numeric()->label('Số lượng'),
                                            TextArea::make('description')->label('Miêu tả')->required(),
                                            FileUpload::make('image')
                                                ->disk('public')
                                                ->directory('event_gifts')
                                                ->label('Hình ảnh')
                                                ->image()
                                                ->maxSize(51200),
                                        ])
                                        ->default([])
                                        ->collapsible(),
                                    Repeater::make('config_game.custom_user_rates')
                                        ->label('Tỉ lệ chỉ định cho người chơi')
                                        ->schema([
                                            Select::make('user_id')
                                                ->label('Người chơi')
                                                ->options(User::pluck('name', 'id'))
                                                ->searchable()
                                                ->required(),
                                            Repeater::make('rates')
                                                ->label('Tỉ lệ quà')
                                                ->schema([
                                                    Select::make('gift_id')
                                                        ->label('Phần quà')
                                                        ->options(fn() => EventGameGift::where('event_game_id', null)->pluck('name', 'id'))
                                                        ->required(),
                                                    TextInput::make('rate')
                                                        ->numeric()
                                                        ->minValue(0)
                                                        ->maxValue(100)
                                                        ->suffix('%')
                                                        ->required(),
                                                ])
                                                ->columns(2)
                                                ->collapsible(),
                                        ])
                                        ->columns(1)
                                        ->default([]),
                                ])
                        ])->from('md')
                    ])
                    ->action(function (array $data) {
                        $game = EventGame::create([
                            ...$data,
                            'event_id' => $this->record->id,
                        ]);

                        $gifts = array_map(function ($gift) use ($game) {
                            $gift['event_game_id'] = $game->id;
                            return $gift;
                        }, $data['gifts'] ?? []);
                        EventGameGift::insert($gifts);

                        Notification::make()
                            ->success()
                            ->title('Tạo thành công')
                            ->body('Trò chơi mới đã được thêm vào sự kiện.')
                            ->send();

                        $this->resetTable();
                    })
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()
                        ->label('Xem')
                        ->schema(fn($record) => [
                            Flex::make([
                                Section::make('Thông tin cơ bản')
                                    ->schema([
                                        TextInput::make('name')
                                            ->label('Tên trò chơi')
                                            ->disabled(),
                                        Textarea::make('description')
                                            ->label('Miêu tả')
                                            ->rows(3)
                                            ->disabled(),
                                        Select::make('game_type')
                                            ->label('Loại trò chơi')
                                            ->options(\App\Utils\Constants\EventGameType::getOptions())
                                            ->disabled(),
                                    ]),
                                Section::make('Cấu hình')
                                    ->schema([
                                        Toggle::make('config_game.require_membership')
                                            ->label('Yêu cầu gói thành viên')
                                            ->disabled(),
                                        Repeater::make('gifts')
                                            ->relationship('gifts')
                                            ->label('Gói quà')
                                            ->schema([
                                                TextInput::make('name')
                                                    ->label('Tên gói quà')
                                                    ->disabled(),
                                                TextInput::make('quantity')
                                                    ->numeric()
                                                    ->label('Số lượng')
                                                    ->disabled(),
                                                Textarea::make('description')
                                                    ->label('Miêu tả')
                                                    ->disabled(),
                                                TextInput::make('rate')
                                                    ->numeric()
                                                    ->label('Tỉ lệ xuất hiện trong kết quả')
                                                    ->minValue(0)
                                                    ->maxValue(99)
                                                    ->disabled(),
                                                FileUpload::make('image')
                                                    ->disk('public')
                                                    ->directory('event_gifts')
                                                    ->label('Hình ảnh')
                                                    ->image()
                                                    ->maxSize(51200),
                                            ])
                                            ->collapsible(),
                                        Repeater::make('config_game.custom_user_rates')
                                            ->label('Tỉ lệ chỉ định cho người chơi')
                                            ->schema([
                                                Select::make('user_id')
                                                    ->label('Người chơi')
                                                    ->options(User::pluck('name', 'id'))
                                                    ->searchable()
                                                    ->required(),
                                                Repeater::make('rates')
                                                    ->label('Tỉ lệ quà')
                                                    ->schema([
                                                        Select::make('gift_id')
                                                            ->label('Phần quà')
                                                            ->options(fn() => EventGameGift::pluck('name', 'id'))
                                                            ->required(),
                                                        TextInput::make('rate')
                                                            ->numeric()
                                                            ->minValue(0)
                                                            ->maxValue(100)
                                                            ->suffix('%')
                                                            ->required(),
                                                    ])
                                                    ->columns(2)
                                                    ->collapsible(),
                                            ])
                                            ->columns(1)
                                            ->default([]),
                                    ]),
                            ])->from('md'),
                        ])
                        ->fillForm(fn($record) => $record->only([
                            'name',
                            'description',
                            'game_type',
                            'config_game',
                        ])),
                    DeleteAction::make()
                        ->recordTitle('trò chơi')
                        ->label('Xóa')
                        ->successRedirectUrl(false)
                        ->action(function ($record) {
                            $record->delete();
                        })
                        ->successNotificationTitle('Đã xóa trò chơi!')
                        ->after(fn() => $this->resetTable()),
                    Action::make('edit')
                        ->label('Sửa')
                        ->icon('heroicon-o-pencil-square')
                        ->schema(
                            fn($record) => [
                                Flex::make([
                                    Section::make('Thông tin cơ bản')
                                        ->schema([
                                            TextInput::make('name')
                                                ->label('Tên trò chơi')
                                                ->required()
                                                ->maxLength(255),
                                            Textarea::make('description')
                                                ->label('Miêu tả')
                                                ->rows(3),
                                            Select::make('game_type')
                                                ->label('Loại trò chơi')
                                                ->options(\App\Utils\Constants\EventGameType::getOptions())
                                                ->required(),
                                        ]),
                                    Section::make('Cấu hình')
                                        ->schema([
                                            Repeater::make('gifts')
                                                ->relationship('gifts')
                                                ->label('Gói quà')
                                                ->schema([
                                                    TextInput::make('name')
                                                        ->label('Tên gói quà')
                                                        ->required(),
                                                    TextInput::make('quantity')
                                                        ->numeric()
                                                        ->label('Số lượng'),
                                                    Textarea::make('description')
                                                        ->label('Miêu tả'),
                                                    FileUpload::make('image')
                                                        ->disk('public')
                                                        ->directory('event_gifts')
                                                        ->label('Hình ảnh')
                                                        ->image()
                                                        ->maxSize(51200)
                                                ])
                                                ->collapsible(),
                                            Repeater::make('config_game.custom_user_rates')
                                                ->label('Tỉ lệ chỉ định cho người chơi')
                                                ->schema([
                                                    Select::make('user_id')
                                                        ->label('Người chơi')
                                                        ->options(User::where('organizer_id', $this->record->organizer_id)->pluck('name', 'id'))
                                                        ->searchable()
                                                        ->required(),
                                                    Repeater::make('rates')
                                                        ->label('Tỉ lệ quà')
                                                        ->schema([
                                                            Select::make('gift_id')
                                                                ->label('Phần quà')
                                                                ->options(
                                                                    fn() => EventGameGift::where('event_game_id', $record->id)
                                                                        ->pluck('name', 'id')
                                                                )
                                                                ->required(),
                                                            TextInput::make('rate')
                                                                ->numeric()
                                                                ->minValue(0)
                                                                ->maxValue(100)
                                                                ->suffix('%')
                                                                ->required(),
                                                        ])
                                                        ->columns(2)
                                                        ->collapsible(),
                                                ])
                                                ->columns(1)
                                                ->default([]),
                                        ]),
                                ])->from('md')
                            ]
                        )
                        ->fillForm(fn($record) => $record->only([
                            'name',
                            'description',
                            'game_type',
                            'config_game',
                        ]))
                        ->color('primary')
                        ->action(function ($record, array $data) {
                            $record->update($data);
                            Notification::make()
                                ->success()
                                ->title('Cập nhật thành công')
                                ->send();
                            $this->resetTable();
                        }),
                    Action::make('link')
                        ->label('Mở trang')
                        ->icon('heroicon-o-link')
                        ->color('primary')
                        ->url(fn($record) => route('game.play', ['id' => $record->id]))
                        ->openUrlInNewTab()
                        ->extraAttributes(['class' => 'copy-url-action'])
                ])
            ]);
    }
}
