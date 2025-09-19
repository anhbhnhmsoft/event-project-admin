<?php

namespace App\Services;

use App\Models\EventArea;
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

    public function deleteArea($areaId)
    {
        DB::beginTransaction();
        try {
            $eventArea = EventArea::find($areaId)->delete();
            DB::commit();
            return $eventArea;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Delete EventArea failed: " . $e->getMessage());
            return null;
        }
    }

    public function updateArea($area)
    {
        DB::beginTransaction();
        try {
            $eventArea = EventArea::find($area['id'])->update($area);
            DB::commit();
            return $eventArea;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Update EventArea failed: " . $e->getMessage());
            return null;
        }
    }
}
