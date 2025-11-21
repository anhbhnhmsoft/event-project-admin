<?php

namespace App\Http\Resources;

use App\Utils\Helper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventListCommentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /**
         * @var $this Event Comment
         */
        $user = $this->user;

        return [
            'id' => (string)$this->id,
            'user_comment' => [
                'id' => $user->id,
                'name' => $user->name,
                'avatar_url'=> $user->avatar_path ? Helper::generateURLImagePath($user->avatar_path) : null
            ],
            'type' => $this->type,
            'content' => $this->content,
            'created_at' => $this->created_at
        ];
    }
}
