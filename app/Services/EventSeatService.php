<?php

namespace App\Services;

use App\Models\Event;
use App\Models\EventSeat;
use App\Utils\Constants\EventSeatStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EventSeatservice
{
    public function eventSeatInsert($seats)
    {
        DB::beginTransaction();
        try {
            $result =  EventSeat::insert($seats);
            DB::commit();
            return $result;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Insert EventSeats failed: " . $e->getMessage());
            return false;
        }
    }
    public function getPaginatedSeats(?array $selectedArea, string|int $seatFilter, int $perPage = 10)
    {
        if (!$selectedArea) {
            return EventSeat::whereRaw('0 = 1')->paginate($perPage, ['*'], 'seatsPage');
        }

        $query = EventSeat::where('event_area_id', $selectedArea['id'])
            ->orderBy('seat_code');

        if ($seatFilter !== 'all') {
            $query->where('status', EventSeatStatus::from($seatFilter)->value);
        }

        return $query->paginate($perPage, ['*'], 'seatsPage');
    }

    public function getSeatById($seatId)
    {
        $result =  EventSeat::with('user')->find($seatId);
        return $result;
    }

    public function deleteSeatsByAreaId($areaId)
    {
        DB::beginTransaction();
        try {
            $result = EventSeat::where('event_area_id', $areaId)->delete();

            DB::commit();
            return $result;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Delete EventSeats failed: " . $e->getMessage());
            return false;
        }
    }

    public function getAssignedUserIds(Event $event): array
    {
        return EventSeat::whereHas('area', function ($q) use ($event) {
            $q->where('event_id', $event->id);
        })
            ->pluck('user_id')
            ->filter()
            ->all();
    }

    public function updateSeat(array $seat)
    {
        DB::beginTransaction();
        try {
            $seatModel = EventSeat::find($seat['id']);

            if (!$seatModel) {
                throw new \Exception("Seat ID {$seat['id']} not found");
            }

            $result = $seatModel->update([
                'user_id' => $seat['user_id'],
                'status'  => $seat['status'],
            ]);

            DB::commit();
            return $result;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Update EventSeats failed: " . $e->getMessage());
            return false;
        }
    }
}
