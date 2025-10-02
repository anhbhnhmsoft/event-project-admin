<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventScheduleDocumentFileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'         => (string) $this->id,
            'file_path'  => $this->file_path,
            'file_name'  => $this->file_extension,
            'file_size'  => $this->file_size,
            'file_type'  => $this->file_type,
        ];
    }
}
