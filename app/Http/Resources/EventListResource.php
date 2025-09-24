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
        $status_history = $this->eventUserHistories()
            ->where('user_id',$request->user()->id)
            ->select('status')
            ->value('status');
        return [
            'id' => (string)$this->id,
            'name' => $this->name,
            'status' => $this->status,
            'image_represent_path' => Helper::generateURLImagePath($this->image_represent_path),
            'address' => $this->address,
            'day_represent' => $this->day_represent,
            'status_history' => $status_history
        ];
    }
}
