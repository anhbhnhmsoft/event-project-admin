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
    protected  $areaService;
    protected  $seatService;
    protected  $eventUserHistoryService;

    public $seatsPerPage = 50;
    public $usersPerPage = 10;

    public $areas = [];
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
    public function boot(EventAreaService $areaService, EventSeatservice $seatService, EventUserHistoryService $eventUserHistoryService)
    {
        $this->areaService             = $areaService;
        $this->seatService             = $seatService;
        $this->eventUserHistoryService = $eventUserHistoryService;
    }
    public function loadAreas()
    {
        $this->areas = $this->event
            ->areas()
            ->with(['seats' => function ($query) {
                $query->orderByRaw('seat_code + 0 asc')->limit(50);
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
            'areaPrice' => function ($attr, $value, $fail) {
                if (!$this->event->free_to_join && ($value === null || $value === '')) {
                    $fail('Vui lòng nhập giá khu vực.');
                }
            },
        ]);
        $countAreas = count($this->areas);
        $area = $this->areaService->eventAreaCreateOne([
            'name' => $this->event->name . " " . ($countAreas + 1),
            'capacity' => (int) $this->areaCapacity,
            'event_id' => $this->event->id,
            'vip' => $this->areaVip,
            'price' => $this->event->free_to_join ? null : (string) $this->areaPrice,
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
            $this->reset(['areaVip', 'areaCapacity', 'areaPrice']);
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
        if (! $this->seatInfo) return;

        DB::beginTransaction();
        try {
            $seat = $this->seatService->getSeatById($this->seatInfo['id']);
            $this->eventUserHistoryService->deleteTicketBySeat($seat->id);

            $unassignResult = $this->seatService->unassignSeat($seat);

            if (! $unassignResult['status']) {
                DB::rollBack();
                Notification::make()->title($unassignResult['message'])->danger()->send();
                return;
            }

            DB::commit();
            Notification::make()->title('Huỷ vé và trả ghế thành công!')->success()->send();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Remove seat + ticket failed: " . $e->getMessage());
            Notification::make()->title('Có lỗi xảy ra khi huỷ vé hoặc ghế.')->danger()->send();
        }

        $this->seatUser = null;
        $this->loadAreas();
    }

    public function assignSeatToUser()
    {
        if (! $this->selectedSeat || ! $this->selectedSeatUser) return;

        DB::beginTransaction();
        try {
            $seatResult = $this->seatService->assignSeatToUser(
                $this->event,
                $this->selectedSeat['id'],
                $this->selectedSeatUser
            );

            if (! $seatResult['status']) {
                DB::rollBack();
                Notification::make()->title($seatResult['message'])->danger()->send();
                return;
            }

            $ticketResult = $this->eventUserHistoryService->createTicket(
                $this->event,
                $this->selectedSeatUser,
                $this->selectedSeat['id']
            );

            if (! $ticketResult['status']) {
                DB::rollBack();
                Notification::make()->title($ticketResult['message'])->danger()->send();
                return;
            }

            DB::commit();
            Notification::make()->title('Ghế và vé đã được tạo thành công!')->success()->send();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Assign seat + ticket failed: " . $e->getMessage());
            Notification::make()->title('Có lỗi xảy ra khi tạo ghế hoặc vé.')->danger()->send();
        }

        $this->reset(['selectedSeat', 'selectedSeatUser']);
        $this->loadAreas();
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

        if ($this->selectedSeat) {
            $this->seatInfo = $this->selectedSeat;
            $this->seatUser = $this->selectedSeat['user'] ?? [];
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
