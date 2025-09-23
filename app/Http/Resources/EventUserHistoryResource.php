<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class EventUserHistoryResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => (string) $this->id,
            'event_id' => (string) $this->event_id,
            'user_id' => (string) $this->user_id,
            'event_seat_id' => (string) $this->event_seat_id,
            'ticket_code' => $this->ticket_code,
            'status' => $this->status,
        ];
    }
}
