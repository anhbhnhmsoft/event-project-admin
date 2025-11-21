<?php

namespace App\Livewire\Filament;

use App\Models\Event;
use Livewire\Attributes\Computed;
use App\Services\EventAreaService;
use App\Services\EventSeatService;
use App\Services\EventUserHistoryService;
use App\Utils\Constants\EventSeatStatus;
use App\Utils\Constants\RoleUser;
use App\Utils\Constants\EventUserHistoryStatus;
use Livewire\Component;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\WithPagination;

class SeatsEvent extends Component
{
    use WithPagination;

    public $event;
    protected $areaService;
    protected $seatService;
    protected $eventUserHistoryService;

    public $seatsPerPage = 50;
    public $usersPerPage = 10;
    public $areasPerPage = 6; // Thêm biến phân trang cho areas

    public $selectedArea = null;
    public $showAreaModal = false;
    public $showSeatModal = false;

    public $hiddenDetailSeat = false;

    public $areaCapacity = '';
    public $areaVip = false;
    public $areaPrice = null;

    public string $newSeatName = '';
    public $selectedSeat = null;
    public $selectedSeatUser = null;

    public $userSearch = '';
    public $seatUser = [];

    public $seatInfo = [];
    public $seatFilter = 'all';

    protected $updatesQueryString = ['userSearch'];

    public function mount(Event $event)
    {
        $this->event = $event;
    }

    public function boot(EventAreaService $areaService, EventSeatservice $seatService, EventUserHistoryService $eventUserHistoryService)
    {
        $this->areaService = $areaService;
        $this->seatService = $seatService;
        $this->eventUserHistoryService = $eventUserHistoryService;
    }

    // Phân trang cho areas
    #[Computed]
    public function paginatedAreas()
    {
        return $this->event
            ->areas()
            ->with([
                'seats' => function ($query) {
                    $query->orderByRaw('seat_code + 0 asc')->limit(50);
                }
            ])
            ->paginate($this->areasPerPage, ['*'], 'areasPage');
    }

    public function updatingUserSearch()
    {
        $this->resetPage('usersPage');
    }

    public function createArea()
    {
        $this->validate([
            'areaCapacity' => 'required|integer|min:1',
            'areaPrice' => function ($attr, $value, $fail) {
                if (!$this->event->free_to_join && ($value === null || $value === '')) {
                    $fail(__('organizer.seats.enter_price'));
                }
            },
        ]);

        $countAreas = $this->event->areas()->count();
        $area = $this->areaService->eventAreaCreateOne([
            'name' => $this->event->name . " " . ($countAreas + 1),
            'capacity' => (int) $this->areaCapacity,
            'event_id' => $this->event->id,
            'vip' => $this->areaVip,
            'price' => $this->event->free_to_join ? null : (string) $this->areaPrice,
        ]);

        if ($area) {
            $seatResult = $this->generateSeats($area);
            if (!$seatResult) {
                Notification::make()
                    ->title(__('organizer.seats.create_seat_failed'))
                    ->danger()
                    ->send();
            }
            $this->reset(['areaVip', 'areaCapacity', 'areaPrice']);
            $this->showAreaModal = false;
            $this->resetPage('areasPage');
            Notification::make()
                ->title(__('organizer.seats.area_created'))
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title(__('organizer.seats.create_failed'))
                ->danger()
                ->send();
        }
    }

    public function toggleSelectedUser($userId)
    {
        $userId = (int) $userId;

        if ($this->selectedSeatUser === $userId) {
            $this->selectedSeatUser = null;
        } else {
            $this->selectedSeatUser = $userId;
        }
    }

    public function updateSeatName()
    {
        if (!$this->seatInfo) {
            return;
        }

        $this->validate([
            'newSeatName' => 'required|string|max:255',
        ]);

        $this->seatInfo['seat_code'] = $this->newSeatName;

        $result = $this->seatService->updateSeat($this->seatInfo);
        $this->newSeatName = '';

        if ($result) {
            return Notification::make()
                ->title(__('organizer.seats.update_success'))
                ->success()
                ->send();
        } else {
            return Notification::make()
                ->title(__('organizer.seats.update_failed'))
                ->danger()
                ->send();
        }
    }

    public function removeSeatUser()
    {
        if (!$this->seatInfo)
            return;

        DB::beginTransaction();
        try {
            $seat = $this->seatService->getSeatById($this->seatInfo['id']);
            $this->eventUserHistoryService->deleteTicketBySeat($seat->id);

            $unassignResult = $this->seatService->unassignSeat($seat);

            if (!$unassignResult['status']) {
                DB::rollBack();
                Notification::make()->title($unassignResult['message'])->danger()->send();
                return;
            }

            DB::commit();
            Notification::make()->title(__('organizer.seats.cancel_ticket_success'))->success()->send();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Remove seat + ticket failed: " . $e->getMessage());
            Notification::make()->title(__('organizer.seats.cancel_ticket_error'))->danger()->send();
        }

        $this->seatUser = null;
    }

    public function assignSeatToUser()
    {
        if (!$this->selectedSeat || !$this->selectedSeatUser)
            return;

        DB::beginTransaction();
        try {
            $seatResult = $this->seatService->assignSeatToUser(
                $this->event,
                $this->selectedSeat['id'],
                $this->selectedSeatUser
            );

            if (!$seatResult['status']) {
                DB::rollBack();
                Notification::make()->title($seatResult['message'])->danger()->send();
                return;
            }

            $ticketResult = $this->eventUserHistoryService->createTicket(
                $this->event,
                $this->selectedSeatUser,
                $this->selectedSeat['id']
            );

            if (!$ticketResult['status']) {
                DB::rollBack();
                Notification::make()->title($ticketResult['message'])->danger()->send();
                return;
            }

            DB::commit();
            Notification::make()->title(__('organizer.seats.assign_success'))->success()->send();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Assign seat + ticket failed: " . $e->getMessage());
            Notification::make()->title(__('organizer.seats.assign_error'))->danger()->send();
        }

        $this->reset(['selectedSeat', 'selectedSeatUser']);
        $this->hiddenDetailSeat = false;
    }

    public function closeDetailSeat()
    {
        $this->reset([
            'selectedSeatUser',
            'selectedSeat',
            'seatInfo',
            'seatUser',
            'userSearch',
        ]);

        $this->resetValidation();
        $this->resetErrorBag();
        $this->hiddenDetailSeat = false;
    }

    private function generateSeats($area): bool
    {
        $seats = [];
        for ($col = 1; $col <= $this->areaCapacity; $col++) {
            $seats[] = [
                'event_area_id' => $area['id'],
                'seat_code' => $col,
                'status' => EventSeatStatus::AVAILABLE->value,
            ];
        }

        return $this->seatService->eventSeatInsert($seats);
    }

    #[Computed]
    public function paginatedEditingSeats()
    {
        return $this->seatService->getPaginatedSeats(
            $this->selectedArea,
            $this->seatFilter,
            $this->seatsPerPage
        );
    }

    public function showDetailSeat($seatId)
    {
        $this->selectedSeat = $this->seatService->getSeatById($seatId)->toArray();
        $this->hiddenDetailSeat = true;

        if ($this->selectedSeat) {
            $this->seatInfo = $this->selectedSeat;
            $this->seatUser = $this->selectedSeat['user'] ?? [];
            $this->hiddenDetailSeat = true;
        } else {
            Notification::make()
                ->title(__('organizer.seats.seat_not_found'))
                ->danger()
                ->send();

            $this->hiddenDetailSeat = false;
        }
    }

    #[Computed]
    public function paginatedUsers()
    {
        return $this->event->organizer->users()
            ->where('role', RoleUser::CUSTOMER->value)
            ->when($this->userSearch, function ($q) {
                $q->where(function ($query) {
                    $query->where('name', 'like', "%{$this->userSearch}%")
                        ->orWhere('email', 'like', "%{$this->userSearch}%")
                        ->orWhere('phone', 'like', "%{$this->userSearch}%");
                });
            })
            ->leftJoin('event_seats', 'users.id', '=', 'event_seats.user_id')
            ->select('users.*')
            ->orderByRaw('CASE WHEN event_seats.user_id IS NULL THEN 0 ELSE 1 END ASC')
            ->paginate($this->usersPerPage, ['*'], 'usersPage');
    }

    public function selectArea($areaId)
    {
        $area = $this->event->areas()->find($areaId);
        if ($area) {
            $this->selectedArea = $area->toArray();
            $this->areaCapacity = $this->selectedArea['capacity'];
            $this->areaVip = $this->selectedArea['vip'];
            $this->showSeatModal = true;
        }
    }

    public function deleteArea($areaId)
    {
        $areaDeleted = $this->areaService->deleteArea((int) $areaId);

        if ($areaDeleted) {
            $this->resetPage('areasPage');
            Notification::make()
                ->title(__('organizer.seats.area_deleted'))
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title(__('organizer.seats.delete_failed'))
                ->danger()
                ->send();
        }
    }

    public function updateArea()
    {
        $this->showSeatModal = false;

        $result = $this->areaService->updateAreaAndSeats($this->selectedArea);

        if ($result) {
            Notification::make()
                ->title(__('organizer.seats.area_updated'))
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title(__('organizer.seats.update_failed'))
                ->danger()
                ->send();
        }
    }

    public function generateSeatsUpdate($selectedArea): bool
    {
        $area = $this->areaService->getAreaById($selectedArea['id']);
        if (!$area) {
            return false;
        }

        return $this->areaService->updateSeatsForArea($area->id, (int) $selectedArea['capacity']);
    }

    public function closeModalEdit()
    {
        $this->reset([
            'showSeatModal',
            'seatFilter',
            'selectedArea',
            'selectedSeat',
            'selectedSeatUser',
            'seatInfo',
            'seatUser',
            'userSearch',
            'hiddenDetailSeat',
        ]);

        $this->seatFilter = 'all';
        $this->areaVip = false;
        $this->areaCapacity = '';
        $this->resetPage('seatsPage');
        $this->resetPage('usersPage');
    }

    public function render()
    {
        return view('livewire.filament.seats-event');
    }
}
