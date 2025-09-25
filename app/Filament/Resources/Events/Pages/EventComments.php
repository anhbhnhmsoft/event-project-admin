<?php

namespace App\Filament\Resources\Events\Pages;

use App\Filament\Resources\Events\EventResource;
use App\Models\Event;
use App\Models\EventComment;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Vite;

class EventComments extends Page implements HasTable
{
    use InteractsWithRecord;

    use InteractsWithTable;

    protected static ?string $title = 'Bình luận';

    protected static ?string $modelLabel = 'Bình luận';

    protected static ?string $pluralModelLabel = 'Bình luận';

    protected static string $resource = EventResource::class;

    protected string $view = 'filament.pages.event-comments';

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
                EventComment::query()->where('event_id', $this->record->id)
            )
            ->columns([
                TextColumn::make('user.name')
                    ->label('Người dùng')
                    ->searchable(),

                TextColumn::make('content')
                    ->label('Nội dung')
                    ->limit(80)
                    ->searchable(),

                TextColumn::make('created_at')
                    ->label('Thời điểm bình luận')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Xem'),
                DeleteAction::make()
                    ->recordTitle('bình luận')
                    ->label('Xóa')
                    ->action(function ($record) {
                        $record->delete();
                    })
                    ->successNotificationTitle('Đã xóa bình luận!')
                    ->after(fn() => $this->resetTable()),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->recordTitle('bình luận')
                        ->label('Xóa')
                        ->successRedirectUrl(fn() => null)
                        ->successNotificationTitle('Đã xóa bình luận!')
                        ->after(fn() => $this->resetTable()),
                ]),
            ]);
    }
}
