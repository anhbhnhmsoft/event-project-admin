<?php

namespace App\Filament\Widgets;

use App\Services\EventService;
use App\Utils\Constants\RoleUser;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Widgets\Widget;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Grid;
use Illuminate\Support\Facades\Auth;

class EventSelectWidget extends Widget implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'filament.widgets.event-select-widget';

    protected int|string|array $columnSpan = [
        'default' => 1,
        'md' => 2,
        'xl' => 3,
    ];

    public static function getHeading(): string
    {
        return 'Bộ lọc sự kiện';
    }

    public ?string $event_id = null;
    public ?string $organizer_id = null;
    public $start_date = null;
    public $end_date = null;
    public string $chart_type = 'hour';

    public function mount(): void
    {
        $user = Auth::user();

        if ($user->role != RoleUser::SUPER_ADMIN) {
            $this->organizer_id = $user->organizer_id;
        } else {
            $this->organizer_id = Filament::getTenant()?->organizer_id ?? session('organizer_id');
            $this->form->fill(['organizer_id' => $this->organizer_id]);
        }

        $this->event_id = Filament::getTenant()?->id ?? session('event_id');
        $this->start_date = Filament::getTenant()?->start_date ?? session('start_date');
        $this->end_date = Filament::getTenant()?->end_date ?? session('end_date');
        $this->chart_type = session('chart_type', 'hour');

        $this->form->fill([
            'event_id' => $this->event_id,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'chart_type' => $this->chart_type,
        ]);
    }

    protected function getFormSchema(): array
    {
        $user = Auth::user();
        /** @var \App\Services\EventService $EventService */
        $eventService = app(EventService::class);
        $eventOptions = $eventService->getEventsListByOrganizerId($this->organizer_id);


        $commonFields = [
            Select::make('event_id')
                ->label('Chọn sự kiện')
                ->options($eventOptions)
                ->searchable()
                ->live()
                ->placeholder('--- Vui lòng chọn một sự kiện ---')
                ->afterStateUpdated(function ($state) {
                    session(['event_id' => $state]);
                    $this->event_id = $state;
                    $this->dispatch('$refresh')->to(\App\Filament\Widgets\EventStatsOverview::class);
                    $this->dispatch('$refresh')->to(\App\Filament\Widgets\EventCheckinChart::class);
                }),

            DatePicker::make('start_date')
                ->label('Từ ngày')
                ->afterStateUpdated(function ($state) {
                    session(['start_date' => $state]);
                    $this->start_date = $state;
                    $this->dispatch('$refresh')->to(\App\Filament\Widgets\EventCheckinChart::class);
                })->columnSpan(1),

            DatePicker::make('end_date')
                ->label('Tới ngày')
                ->afterStateUpdated(function ($state) {
                    session(['end_date' => $state]);
                    $this->end_date = $state;
                    $this->dispatch('$refresh')->to(\App\Filament\Widgets\EventCheckinChart::class);
                })->columnSpan(1),

            Select::make('chart_type')
                ->label('Loại thống kê')
                ->options([
                    'hour' => 'Theo Giờ (Cộng dồn)',
                    'day' => 'Theo Ngày',
                ])
                ->default('hour')
                ->live()
                ->afterStateUpdated(function ($state) {
                    session(['chart_type' => $state]);
                    $this->chart_type = $state;
                    $this->dispatch('$refresh')->to(\App\Filament\Widgets\EventCheckinChart::class);
                })->columnSpan(1),
        ];

        if ($user->role == RoleUser::SUPER_ADMIN->value) {
            array_unshift(
                $commonFields,
                Select::make('organizer_id')
                    ->label('Chọn tổ chức')
                    ->options($eventService->getAllOrganizersList())
                    ->searchable()
                    ->live()
                    ->placeholder('--- Vui lòng chọn một tổ chức ---')
                    ->afterStateUpdated(function ($state, callable $set) {
                        session(['organizer_id' => $state]);

                        $set('event_id', null);
                        session(['event_id' => null]);

                        $this->dispatch('$refresh')->to(\App\Filament\Widgets\EventStatsOverview::class);
                        $this->dispatch('$refresh')->to(\App\Filament\Widgets\EventCheckinChart::class);
                    })
            );

            return [
                Grid::make(['default' => 1, 'md' => 4])
                    ->schema($commonFields),
            ];
        }

        if ($user->role == RoleUser::ADMIN->value) {
            return [
                Grid::make(['default' => 1, 'md' => 3])
                    ->schema([
                        $commonFields[0],
                        $commonFields[1],
                    ]),
            ];
        }

        return [];
    }
}
