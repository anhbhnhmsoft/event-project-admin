<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class EventScheduleDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $allowDocument = $this->additional['allowDocument'] ?? false;
        return [
            'id' => (string)$this->id,
            'title' => $this->title,
            'description' => $this->description,
            'start_time' => Carbon::make($this->start_time)->format('H:i'),
            'end_time' => Carbon::make($this->end_time)->format('H:i'),
            'documents' => $allowDocument
                ? $this->documents->select(['id', 'title'])->map(function ($document) {
                    return [
                        'id' => (string)$document['id'],
                        'title' => $document['title'],
                    ];
                })
                : [],
        ];
    }
}
