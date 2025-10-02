<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventScheduleDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $allowDocument = $this->additional['allowDocument'] ?? true;

        return [
            'id'          => (string) $this->id,
            'title'       => $this->title,
            'description' => $this->description,
            'start_time'  => $this->start_time,
            'end_time'    => $this->end_time,
            'sort'        => $this->sort,
            'documents'   => $allowDocument
                ? EventScheduleDocumentResource::collection($this->whenLoaded('documents'))
                : [],
        ];
    }
}
