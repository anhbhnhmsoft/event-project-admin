<?php

namespace App\Http\Resources;

use App\Utils\Helper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventGameGiftDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => (string) $this->id,   
            'name'        => $this->name,
            'quantity'    => $this->quantity,
            'rate'        => $this->rate,
            'description' => $this->description,
            'image'       => $this->image,
        ];
    }
}
