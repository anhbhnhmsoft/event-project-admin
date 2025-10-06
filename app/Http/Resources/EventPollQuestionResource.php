<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventPollQuestionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => (string) $this->id,
            'event_poll_id' => (string) $this->event_poll_id,
            'type'        => $this->type,
            'question'    => $this->question,
            'order'       => $this->order,
            'options'     => EventPollQuestionOptionResource::collection($this->whenLoaded('options')),
        ];
    }
}
