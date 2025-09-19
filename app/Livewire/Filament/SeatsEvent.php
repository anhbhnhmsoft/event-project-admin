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

    public $showSeatUser = false;
    public $showAssignUser = false;

    public $areaCapacity = '';
    public $areaSeatsPerRow = 10;
    public $areaVip = false;

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
        $this->areas = $this->event->areas()->with('seats')->get()->toArray();
    }

    public function updatingUserSearch()
    {
        $this->resetPage();
    }

    public function createArea()
    {
        $this->validate([
            'areaCapacity' => 'required|integer|min:1',
            'areaSeatsPerRow'  => 'required|integer|min:1'

        ]);
        $countAreas = count($this->areas);
        $area = $this->areaService->eventAreaCreateOne([
            'name' => $this->event->name . ($countAreas + 1),
            'capacity' => (int) $this->areaCapacity,
            'event_id' => $this->event->id,
            'seats_per_row' => (int) $this->areaSeatsPerRow,
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
            $this->reset(['areaVip', 'areaCapacity', 'areaSeatsPerRow']);
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


    public function selectSeat($seatId)
    {
        $this->selectedSeat = $this->seatService->getSeatById($seatId)->toArray();
        $this->showAssignUser = true;
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
            if (!$this->seatService->updateSeat($this->selectedSeat)) {
                Notification::make()
                    ->title('Có lỗi xảy, vui lòng thử lại sau!')
                    ->success()
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

            $this->showAssignUser = false;
        }
        return;
    }

    private function generateSeats($area): bool
    {
        $rowArea = ceil((int) $this->areaCapacity / (int) $this->areaSeatsPerRow);
        $seats = [];
        for ($row = 0; $row < $rowArea; $row++) {
            $rowLetter = $this->getRowLetter($row);
            $seatsInThisRow = ($row == $rowArea  - 1)
                ? (int) $this->areaCapacity - ($row * (int) $this->areaSeatsPerRow)
                : (int) $this->areaSeatsPerRow;
            for ($col = 1; $col <= $seatsInThisRow; $col++) {
                $seats[] = [
                    'event_area_id' => $area['id'],
                    'seat_code'     => $rowLetter . $col,
                    'status'        => EventSeatStatus::AVAILABLE->value,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ];
            }
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
        $seat = $this->seatService->getSeatById($seatId);

        if ($seat) {
            $this->seatInfo = $seat->toArray();
            $this->seatUser = $seat->user ? $seat->user->toArray() : [];
            $this->showSeatUser = true;
        } else {
            Notification::make()
                ->title('Không tồn tại chỗ ngồi!')
                ->danger()
                ->send();

            $this->showSeatUser = false;
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
            ->paginate($this->usersPerPage, ['*'], 'usersPage');
    }


    public function selectArea($areaId)
    {
        $this->selectedArea = collect($this->areas)->firstWhere('id', $areaId);
        $this->areaCapacity = $this->selectedArea['capacity'];
        $this->areaSeatsPerRow = $this->selectedArea['seats_per_row'];
        $this->areaVip = $this->selectedArea['vip'];
        $this->showSeatModal = true;

        return;
    }

    public function deleteArea($areaId)
    {
        $result = $this->areaService->deleteArea((int) $areaId);

        $this->loadAreas();
        if ($result) {

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
        $originalArea = $this->areaService->getAreaById($this->selectedArea['id']);
        $nameChange         = $originalArea['name'] != $this->selectedArea['name'];
        $capacityChange     = $originalArea['capacity'] != $this->selectedArea['capacity'];
        $seatPerRowChange   = $originalArea['seats_per_row'] != $this->selectedArea['seats_per_row'];
        $this->selectedArea['vip'] = $this->areaVip;
        $vipChange          = $originalArea['vip'] != $this->areaVip;
        $this->showSeatModal = false;
        if ($seatPerRowChange || $capacityChange) {

            $this->areaCapacity     = $this->selectedArea['capacity'];
            $this->areaSeatsPerRow = $this->selectedArea['seats_per_row'];

            $result = $this->areaService->updateArea($this->selectedArea);
            if ($result) {
                $resultSeatRemove = $this->seatService->deleteSeatsByAreaId($this->selectedArea['id']);

                if ($resultSeatRemove && $this->generateSeats($this->selectedArea)) {

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
            } else {
                Notification::make()
                    ->title('Cập nhật không thành công!')
                    ->danger()
                    ->send();
            }
        } else if ($nameChange || $vipChange) {
            $result = $this->areaService->updateArea($this->selectedArea);

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
        }

        $this->loadAreas();
        return;
    }

    private function getRowLetter(int $index): string
    {
        $letters = '';
        while ($index >= 0) {
            $letters = chr($index % 26 + 65) . $letters;
            $index = intdiv($index, 26) - 1;
        }
        return $letters;
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
            'showSeatUser',
            'showAssignUser',
        ]);

        $this->seatFilter = 'all';
        $this->areaSeatsPerRow = 10;
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
