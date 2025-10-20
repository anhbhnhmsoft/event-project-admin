<?php

namespace App\Filament\Widgets;

use App\Services\EventService;
use App\Utils\Constants\RoleUser;
use App\Utils\Constants\UnitDurationType;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Widgets\Widget;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Components\Grid;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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
        return __('common.resource.dashboard.select.heading');
    }

    public ?string $event_id = null;
    public ?string $organizer_id = null;
    public $start_date = null;
    public $end_date = null;
    public int $chart_type = UnitDurationType::HOUR->value;

    public function mount(): void
    {
        $user = Auth::user();

        if ($user->role != RoleUser::SUPER_ADMIN) {
            $this->organizer_id = $user->organizer_id;
        } else {
            $this->organizer_id = session('organizer_id');
        }
        $this->event_id = session('event_id');
        $this->start_date = session('start_date');
        $this->end_date = session('end_date');
        $this->chart_type = session('chart_type', UnitDurationType::HOUR->value) ?? UnitDurationType::HOUR->value;

        $this->form->fill([
            'organizer_id' => $this->organizer_id,
            'event_id' => $this->event_id,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'chart_type' => $this->chart_type,
        ]);

        if ($this->event_id) {
            $this->broadcastFilterUpdate();
        }
    }

    protected function broadcastFilterUpdate(): void
    {
        $filterData = [
            'event_id' => $this->event_id,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'chart_type' => $this->chart_type,
            'organizer_id' => $this->organizer_id,
        ];

        $this->dispatch('eventFilterUpdated', $filterData);
    }

    protected function getFormSchema(): array
    {
        $user = Auth::user();
        /** @var \App\Services\EventService $EventService */
        $eventService = app(EventService::class);

        $commonFields = [
            Select::make('event_id')
                ->label(__('common.resource.dashboard.select.choose_event_label'))
                ->options(function ($get) use ($eventService) {
                    $organizerId = $get('organizer_id') ?? $this->organizer_id;
                    if (!$organizerId) {
                        return [];
                    }
                    return $eventService->getEventsListByOrganizerId($this->organizer_id);
                })
                ->searchable()
                ->live()
                ->placeholder(__('common.resource.dashboard.select.event_placeholder'))
                ->afterStateUpdated(function ($state) {
                    $this->event_id = $state;
                    session(['event_id' => $state]);
                    $this->broadcastFilterUpdate();
                }),

            DatePicker::make('start_date')
                ->label(__('common.resource.dashboard.select.start_date_label'))
                ->live()
                ->afterStateUpdated(function ($state) {
                    $this->start_date = $state;
                    session(['start_date' => $state]);
                    $this->broadcastFilterUpdate();
                })
                ->columnSpan(1),

            DatePicker::make('end_date')
                ->label(__('common.resource.dashboard.select.end_date_label'))
                ->live()
                ->afterStateUpdated(function ($state) {
                    $this->end_date = $state;
                    session(['end_date' => $state]);
                    $this->broadcastFilterUpdate();
                })
                ->columnSpan(1),

            Select::make('chart_type')
                ->label(__('common.resource.dashboard.select.chart_type_label'))
                ->options([
                    UnitDurationType::HOUR->value => __('common.resource.dashboard.select.chart_type_hour'),
                    UnitDurationType::DAY->value => __('common.resource.dashboard.select.chart_type_day')    ,
                ])
                ->default(UnitDurationType::HOUR->value)
                ->live()
                ->afterStateUpdated(function ($state) {
                    $this->chart_type = (int) $state;
                    session(['chart_type' => $state]);

                    $this->broadcastFilterUpdate();
                })
                ->columnSpan(1),
        ];

        if ($user->role == RoleUser::SUPER_ADMIN->value) {
            array_unshift(
                $commonFields,
                Select::make('organizer_id')
                    ->label(__('common.resource.dashboard.select.organizer_label'))
                    ->options($eventService->getAllOrganizersList())
                    ->searchable()
                    ->live()
                    ->placeholder( (string) __('common.resource.dashboard.select.organizer_placeholder'))
                    ->afterStateUpdated(function ($state, callable $set) {
                        $this->organizer_id = $state;
                        session(['organizer_id' => $state]);

                        $this->event_id = null;
                        $set('event_id', null);
                        session(['event_id' => null]);

                        $this->broadcastFilterUpdate();
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
                    ->schema($commonFields),
            ];
        }

        return [];
    }
}
