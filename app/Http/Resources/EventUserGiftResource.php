<?php

namespace App\Http\Resources;

use App\Utils\Helper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventUserGiftResource extends JsonResource
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
            'user' => [
                'id'    => (string)$this->user->id,
                'name'  => $this->user->name,
                'email' => $this->user->email,
            ],
            'gift' => [
                'id'          => (string)$this->gift->id,
                'name'        => $this->gift->name,
                'description' => $this->gift->description,
                'image_url'   => $this->gift->image ,
            ],
            'created_at' => $this->created_at,
        ];
    }
}
