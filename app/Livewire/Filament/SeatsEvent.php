<?php

namespace App\Livewire\Filament;

use App\Models\Event;
use Livewire\Attributes\Computed;
use App\Services\EventAreaService;
use App\Services\EventSeatservice;
use App\Utils\Constants\EventSeatStatus;
use App\Utils\Constants\RoleUser;
use Livewire\Component;
use Filament\Notifications\Notification;
use Livewire\WithPagination;

class SeatsEvent extends Component
{
    use WithPagination;

    public $event;
    protected  $areaService;
    protected  $seatService;

    public $seatsPerPage = 50;
    public $usersPerPage = 10;

    public $areas = [];
    public $selectedArea = null;
    public $showAreaModal = false;
    public $showSeatModal = false;

    public $hiddenDetailSeat = false;

    public $areaCapacity = '';
    public $areaVip = false;

    public string $newSeatName = '';
    public $selectedSeat = null;
    public $selectedSeatUser = null;

    public $users = [];
    public $userSearch = '';
    public $seatUser = [];

    public $seatInfo = [];
    public $seatFilter = 'all';

    protected $updatesQueryString = ['userSearch'];
    public function mount(Event $event)
    {
        $this->event = $event;
        $this->users = $event->organizer->users
            ->where('role', RoleUser::CUSTOMER->value)
            ->values()
            ->toArray();
        $this->loadAreas();
    }
    public function boot(EventAreaService $areaService, EventSeatservice $seatService)
    {
        $this->areaService = $areaService;
        $this->seatService = $seatService;
    }
    public function loadAreas()
    {
        $this->areas = $this->event
            ->areas()
            ->with(['seats' => function ($query) {
                $query->orderBy('created_at')->limit(50);
            }])
            ->get()
            ->toArray();
    }

    public function updatingUserSearch()
    {
        $this->resetPage();
    }

    public function createArea()
    {
        $this->validate([
            'areaCapacity' => 'required|integer|min:1',
        ]);
        $countAreas = count($this->areas);
        $area = $this->areaService->eventAreaCreateOne([
            'name' => $this->event->name . ($countAreas + 1),
            'capacity' => (int) $this->areaCapacity,
            'event_id' => $this->event->id,
            'vip' => $this->areaVip
        ]);
        if ($area) {

            $seatResult =  $this->generateSeats($area);
            if ($seatResult) {
            } else {
                Notification::make()
                    ->title('Tạo chỗ ngồi không thành công!')
                    ->danger()
                    ->send();
            }
            $this->reset(['areaVip', 'areaCapacity']);
            $this->showAreaModal = false;
            $this->loadAreas();
            Notification::make()
                ->title('Khu vực đã được tạo thành công!')
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Tạo không thành công!')
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
        if (! $this->seatInfo) {
            return;
        }

        $this->validate([
            'newSeatName' => 'required|string|max:255',
        ]);

        $this->seatInfo['seat_code'] = $this->newSeatName;

        $result = $this->seatService->updateSeat($this->seatInfo);
        $this->newSeatName = '';
        $this->loadAreas();
        if ($result) {
            return  Notification::make()
                ->title('Cập nhật thành công!')
                ->success()
                ->send();
        } else {
            return  Notification::make()
                ->title('Cập nhật không thành công!')
                ->danger()
                ->send();
        }
    }

    public function removeSeatUser()
    {
        if (! $this->seatInfo) {
            return;
        }
        $this->seatInfo['user_id'] = null;
        $this->seatInfo['status'] = EventSeatStatus::AVAILABLE->value;
        $result = $this->seatService->updateSeat($this->seatInfo);
        $this->seatUser = null;
        $this->loadAreas();
        if ($result) {
            return  Notification::make()
                ->title('Cập nhật thành công!')
                ->success()
                ->send();
        } else {
            return  Notification::make()
                ->title('Cập nhật không thành công!')
                ->danger()
                ->send();
        }
    }

    public function assignSeatToUser()
    {
        if ($this->selectedSeat && $this->selectedSeatUser) {
            $alreadyAssigned = in_array(
                $this->selectedSeatUser,
                $this->seatService->getAssignedUserIds($this->event)
            );

            if ($alreadyAssigned) {
                Notification::make()
                    ->title('Người dùng này đã có ghế trong sự kiện!')
                    ->danger()
                    ->send();
                return;
            }

            $this->selectedSeat['status'] = EventSeatStatus::BOOKED->value;
            $this->selectedSeat['user_id'] = $this->selectedSeatUser;

            if (! $this->seatService->updateSeat($this->selectedSeat)) {
                Notification::make()
                    ->title('Có lỗi xảy ra, vui lòng thử lại sau!')
                    ->danger()
                    ->send();
                return;
            }

            $this->selectedSeat = null;
            $this->selectedSeatUser = null;
            $this->loadAreas();

            if ($this->selectedArea) {
                $this->selectedArea = collect($this->areas)->firstWhere('id', $this->selectedArea['id']);
            }

            Notification::make()
                ->title('Ghế đã được gán cho người dùng!')
                ->success()
                ->send();

            $this->hiddenDetailSeat = false;
            $this->selectedSeatUser = null;
        }
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

        for ($col = 1; $col <= $this->areaCapacity; $col++) {
            $seats[] = [
                'event_area_id' => $area['id'],
                'seat_code'     => $col,
                'status'        => EventSeatStatus::AVAILABLE->value,
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
        $seat = $this->seatService->getSeatById($seatId);

        if ($seat) {
            $this->seatInfo = $seat->toArray();
            $this->seatUser = $seat->user ? $seat->user->toArray() : [];
            $this->hiddenDetailSeat = true;
        } else {
            Notification::make()
                ->title('Không tồn tại chỗ ngồi!')
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

        $this->selectedArea = collect($this->areas)->firstWhere('id', $areaId);
        $this->areaCapacity = $this->selectedArea['capacity'];
        $this->areaVip = $this->selectedArea['vip'];
        $this->showSeatModal = true;

        return;
    }

    public function deleteArea($areaId)
    {
        $areaDeleted = $this->areaService->deleteArea((int) $areaId);
        $this->loadAreas();
        if ($areaDeleted) {

            Notification::make()
                ->title('Khu vực đã được xóa!')
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Xóa không thành công!')
                ->danger()
                ->send();
        }
        return;
    }

    public function updateArea()
    {
        $this->showSeatModal = false;

        $result = $this->areaService->updateAreaAndSeats($this->selectedArea);

        if ($result) {
            Notification::make()
                ->title('Khu vực đã được cập nhật!')
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Cập nhật không thành công!')
                ->danger()
                ->send();
        }

        $this->loadAreas();
    }


    public function generateSeatsUpdate($selectedArea): bool
    {
        $area = $this->areaService->getAreaById($selectedArea['id']);
        if (! $area) {
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
        return;
    }


    public function render()
    {
        return view('livewire.filament.seats-event');
    }
}
