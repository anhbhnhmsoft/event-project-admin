<?php

namespace App\Filament\Resources\Events\Pages;

use App\Filament\Resources\Events\EventResource;
use App\Filament\Resources\Events\Schemas\EventGameFormSchema;
use App\Filament\Traits\CheckPlanBeforeAccess;
use App\Models\Event;
use App\Models\EventGame;
use App\Models\EventGameGift;
use App\Services\EventGameService;
use App\Utils\Constants\EventGameType;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Arr;

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
                    ->schema(fn() => EventGameFormSchema::make($this->record, 'create'))
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
                        ->schema(fn($record) => EventGameFormSchema::make($this->record, 'view'))
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
                        ->schema(fn($record) => EventGameFormSchema::make($this->record, 'edit'))
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
