<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class EventUserHistoryResource extends JsonResource
{
    public function toArray($request): array
    {
        $seat = $this->seat ?? null;
        if ($seat){
            $area = $seat->area;
            $seat = [
                'id' => (string)$seat->id,
                'seat_code' => (string)$seat->seat_code,
                'area_id' => (string)$area->id,
                'area_name' => (string)$area->name,
            ];
        }
        return [
            'id' => (string) $this->id,
            'event_id' => (string) $this->event_id,
            'user_id' => (string) $this->user_id,
            'seat' => $seat,
            'ticket_code' =>(string) $this->ticket_code,
            'status' => $this->status,
        ];
    }
}
