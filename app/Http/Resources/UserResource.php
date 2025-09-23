<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Utils\Helper;

class UserResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => (string)$this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'introduce' => $this->introduce,
            'gender' => $this->gender,
            'avatar_url' => $this->avatar_path ? Helper::generateURLImagePath($this->avatar_path) : null,
            'organizer_id' => $this->organizer_id,
            'lang' => $this->lang,
            'membership'  => $this->whenLoaded('activeMemberships', function () {
                return $this->memberships->first();
            }),
        ];
    }
}
