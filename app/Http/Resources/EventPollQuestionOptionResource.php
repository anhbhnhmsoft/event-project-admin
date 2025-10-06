<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventPollQuestionOptionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                     => (string)  $this->id,
            'event_poll_question_id' => (string)  $this->event_poll_question_id,
            'label'                  => $this->label,
            'order'                  => $this->order,
            'is_correct'             => (bool) $this->is_correct,
        ];
    }
}
