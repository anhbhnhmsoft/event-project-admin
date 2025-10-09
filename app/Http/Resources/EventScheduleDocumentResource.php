<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventScheduleDocumentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'            => (string) $this->id,
            'title'         => $this->title,
            'event_schedule_id' => (string) $this->event_schedule_id,
            'event_name' => $this->eventSchedule->event->name,
            'description'   => $this->description,
            'files'       => EventScheduleDocumentFileResource::collection($this->files) ?? [],
        ];
    }
}
