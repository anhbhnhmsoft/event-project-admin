<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GiftUserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $gift = $this->gift;
        $event = $gift->eventGame->event;
        return [
            'id' => (string)$this->id,
            'event' => [
                'id'  => (string)$event->id,
                'name' => $event->name,
            ],
            'gift' => [
                'id' => (string)$gift->id,
                'name' => $gift->name,
                'description' => (string)$gift->description,
            ],
            'created_at' => $this->created_at
        ];
    }
}
