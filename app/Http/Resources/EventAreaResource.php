<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventAreaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => (string)$this->id,
            'name' => (string)$this->name,
            'capacity' => (int)$this->capacity,
            'vip' => $this->vip,
            'seat_available_count' => $this->seat_available_count ?? 0,
            'price' => $this->price ?? null
        ];
    }
}
