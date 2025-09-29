<?php

namespace App\Filament\Resources\Events\Pages;

use App\Filament\Resources\Events\EventResource;
use App\Models\EventGame;

use App\Utils\Constants\EventGameType;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
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
use Illuminate\Database\Eloquent\Model;
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
                                ->schema([])
                        ])
                    ])
                    ->action(function (array $data) {
                        EventGame::create([
                            ...$data,
                            'event_id' => $this->record->id,
                        ]);

                        Notification::make()
                            ->success()
                            ->title('Tạo thành công')
                            ->body('Trò chơi mới đã được thêm vào sự kiện.')
                            ->send();

                        $this->resetTable();
                    })
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Xem'),
                DeleteAction::make()
                    ->recordTitle('trò chơi')
                    ->label('Xóa')
                    ->successRedirectUrl(false)
                    ->action(function ($record) {
                        $record->delete();
                    })
                    ->successNotificationTitle('Đã xóa trò chơi!')
                    ->after(fn() => $this->resetTable()),
                EditAction::make()
            ]);
    }
}
