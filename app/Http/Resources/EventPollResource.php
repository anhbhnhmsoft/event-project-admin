<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class EventPollResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {   
        $now = Carbon::now();

        return [
            'id'            => (string) $this->id,
            'title'         => $this->title,
            'start_time'    => $this->start_time,
            'end_time'      => $this->end_time,
            'duration'      => $this->duration,
            'duration_unit' => $this->duration_unit,
            'is_active'     => (bool) $this->is_active,
            'questions'     => ( $this->start_time <= $now && $this->is_active && $this->end_time >= $now) ?  EventPollQuestionResource::collection($this->whenLoaded('questions')) : [],
            'users'         => UserResource::collection($this->whenLoaded('users')),
        ];
    }
}
