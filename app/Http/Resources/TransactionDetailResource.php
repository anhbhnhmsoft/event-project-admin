<?php

namespace App\Http\Resources;

use App\Utils\Helper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->id,
            'foreign_id' => (string) $this->foreign_id,
            'user_id' => (string) $this->user_id,
            'type' => $this->type,
            'money' => $this->money,
            'transaction_code' => $this->transaction_code,
            'transaction_id' => (string) $this->transaction_id,
            'description' => $this->description,
            'status' => $this->status,
            'metadata' => $this->metadata,
            'config_pay' => $this->config_pay,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
