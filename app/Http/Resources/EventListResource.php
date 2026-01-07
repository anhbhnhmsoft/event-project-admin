<?php

namespace App\Http\Resources;

use App\Models\Event;
use App\Utils\Constants\EventUserHistoryStatus;
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
        /**
         * @var $this Event
         */
        $user = auth('sanctum')->user();
        // nếu đăng nhập thì sẽ lấy status_history của user đó
        $status_history = $user ? $this->eventUserHistories()
            ->where('user_id',$user->id)
            ->select('status')
            ->value('status') : null;
        return [
            'id' => (string)$this->id,
            'name' => $this->name,
            'status' => $this->status,
            'image_represent_path' => Helper::generateURLImagePath($this->image_represent_path),
            'address' => $this->address,
            'day_represent' => $this->day_represent,
            'free_to_join' => $this->free_to_join,
            'status_history' => $status_history
        ];
    }
}
