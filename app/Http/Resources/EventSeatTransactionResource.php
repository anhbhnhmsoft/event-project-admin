<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventSeatTransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'trans_id' => (string) $this->id,
            'expired_at' => $this->expired_at,
            'config_pay' => $this->config_pay,
            'money' => (string) $this->money,
            'description' => $this->description,
        ];
    }
}


