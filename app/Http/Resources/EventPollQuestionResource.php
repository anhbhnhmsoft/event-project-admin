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
        $questions = $this->whenLoaded('questions')->map(function ($question) {
            return [
                'id' => (string) $question->id,
                'question' => $question->question,
                'type' => $question->type,
                'options' => $question->options->map(function ($option) {
                    return [
                        'id' => (string) $option->id,
                        'label' => $option->label,
                    ];
                }),
            ];
        });
        return [
            'id'          => (string) $this->id,
            'event_id' => (string) $this->event_id,
            'title'       => $this->title,
            'start_time'  => $this->start_time,
            'end_time'    => $this->end_time,
            'duration_unit' => $this->duration_unit,
            'duration'      => $this->duration,
            'is_active'   => $this->is_active,
            'questions' => $questions,
        ];
    }
}
