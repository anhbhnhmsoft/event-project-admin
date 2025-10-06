<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventPollVoteResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                           => (string) $this->id,
            'user_id'                      => (string)  $this->user_id,
            'event_poll_question_id'       => (string)  $this->event_poll_question_id,
            'event_poll_question_option_id' => (string)  $this->event_poll_question_option_id,
        ];
    }
}
