<?php

namespace App\Http\Resources;

use App\Utils\Helper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MembershipListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => (string)$this->id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'duration' => $this->duration,
            'badge' => $this->badge,
            'sort' => $this->sort,
            'badge_color_background' => $this->badge_color_background,
            'badge_color_text' => $this->badge_color_text,
            'config' => $this->config,
            'status' => $this->status,
            'type'   => $this->type,
            'organizer_id' => $this->organizer_id
        ];
    }
}
