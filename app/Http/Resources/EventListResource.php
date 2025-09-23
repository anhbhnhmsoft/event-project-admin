<?php

namespace App\Http\Resources;

use App\Utils\Helper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventListResource extends JsonResource
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
            'image_represent_path' => Helper::generateURLImagePath($this->image_represent_path),
            'address' => $this->address,
            'day_represent' => $this->day_represent,
        ];
    }
}
