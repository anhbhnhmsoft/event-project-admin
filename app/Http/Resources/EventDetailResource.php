<?php

namespace App\Http\Resources;

use App\Utils\Helper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class EventDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $organizer = $this->organizer ? [
            'id' => (string)$this->organizer->id,
            'name' => $this->organizer->name,
            'description' => $this->organizer->description,
            'url_image' => Helper::generateURLImagePath($this->organizer->image ?? ''),
        ] : null;

        $userEvent = $this->participants->map(function ($participant) {
            return [
                'id' => (string)$participant->user?->id,
                'name' => $participant->user?->name,
                'avatar_url' => $participant->user?->avatar_path ? Helper::generateURLImagePath($participant->user?->avatar_path) : null,
                'role' => $participant->role,
            ];
        })->values()->all();

        $schedules = $this->schedules->sortBy('sort')->map(function ($schedule) {
            return [
                'id' => (string)$schedule->id,
                'name' => $schedule->title,
            ];
        })->values()->all();

        return [
            'id' => (string)$this->id,
            'organizer' => $organizer,
            'name' => $this->name,
            'short_description' => $this->short_description,
            'description' => $this->description,
            'day_represent' => $this->day_represent ? Carbon::parse($this->day_represent)->format('Y-m-d') : null,
            'start_time' => $this->start_time ? Carbon::parse($this->start_time)->format('H:i') : null,
            'end_time' => $this->end_time ? Carbon::parse($this->end_time)->format('H:i') : null,
            'image_represent_path' => Helper::generateURLImagePath($this->image_represent_path ?? ''),
            'status' => $this->status,
            'address' => $this->address,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'free_to_join' => $this->free_to_join,
            'user_event' => $userEvent,
            'schedules' => $schedules,
        ];
    }
}


