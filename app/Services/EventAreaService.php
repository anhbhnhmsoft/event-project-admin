<?php

namespace App\Services;

use App\Models\EventArea;
use App\Models\EventSeat;
use App\Utils\Constants\EventSeatStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EventAreaService
{
    public function eventAreaCreateOne($area)
    {
        DB::beginTransaction();
        try {
            $eventArea = EventArea::create($area);
            DB::commit();
            return $eventArea;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Create EventArea failed: " . $e->getMessage());
            return null;
        }
    }

    public function getAreaById($areaId)
    {
        return EventArea::find($areaId);
    }

    public function deleteArea(int $areaId)
    {
        DB::beginTransaction();
        try {
            $hasAssignedSeats = EventSeat::where('event_area_id', $areaId)
                ->whereNotNull('user_id')
                ->exists();

            if ($hasAssignedSeats) {
                DB::rollBack();
                return false;
            }

            EventSeat::where('event_area_id', $areaId)->delete();
            EventArea::find($areaId)?->delete();

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Delete EventArea failed: " . $e->getMessage());
            return false;
        }
    }

    public function updateArea($area)
    {
        DB::beginTransaction();
        try {
            $eventArea = EventArea::find((int) $area['id'])->update($area);
            DB::commit();
            return $eventArea;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Update EventArea failed: " . $e->getMessage());
            return null;
        }
    }

    public function updateAreaAndSeats(array $areaData): bool
    {
        DB::beginTransaction();
        try {
            $area = EventArea::find((int) $areaData['id']);
            if (! $area) {
                throw new \Exception("Area not found");
            }
            $oldCapacity = $area->capacity;

            $area->update($areaData);

            $capacityChanged = $oldCapacity != (int) $areaData['capacity'];

            if ($capacityChanged) {
                $ok = $this->updateSeatsForArea($area->id, (int) $areaData['capacity']);
                if (! $ok) {
                    throw new \Exception("Update seats failed");
                }
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("updateAreaAndSeats failed: " . $e->getMessage());
            return false;
        }
    }


    public function updateSeatsForArea($areaId, int $newCapacity): bool
    {
        $area = EventArea::find($areaId);
        if (! $area) {
            return false;
        }

        $currentCapacity = $area->seats()->count();


        if ($newCapacity == $currentCapacity) {
            return true;
        }
        if ($newCapacity > $currentCapacity) {

            $seatsToAdd = $newCapacity - $currentCapacity;
            $insert = [];

            $maxSeatNumber = (int) $area->seats()
                ->whereRaw('seat_code REGEXP "^[0-9]+$"')
                ->max(DB::raw('CAST(seat_code AS UNSIGNED)'));

            $maxSeatNumber = $maxSeatNumber ?: 0;

            for ($i = 1; $i <= $seatsToAdd; $i++) {
                $insert[] = [
                    'event_area_id' => $area->id,
                    'seat_code'     => (string) ($maxSeatNumber + $i),
                    'status'        => EventSeatStatus::AVAILABLE->value,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ];
            }

            if (! empty($insert)) {
                EventSeat::insert($insert);
            }

            return true;
        }

        if ($newCapacity < $currentCapacity) {
            $seatsToRemove = $currentCapacity - $newCapacity;

            $emptySeatsQuery = $area->seats()
                ->whereNull('user_id')
                ->where('status', EventSeatStatus::AVAILABLE->value)
                ->orderByDesc('id');

            if ($emptySeatsQuery->count() < $seatsToRemove) {
                return false;
            }

            $toDelete = $emptySeatsQuery->take($seatsToRemove)->get();
            foreach ($toDelete as $seat) {
                $seat->delete();
            }

            return true;
        }

        return true;
    }
}
