<?php

namespace App\Services;

use App\Models\Organizer;
use App\Exceptions\ServiceException;
use Illuminate\Database\Eloquent\Collection;

class OrganizerService
{
    public function getActive(): Collection
    {
        return Organizer::whereNull('deleted_at')->get();
    }

    public function getActiveOptions(): array
    {
        return $this->getActive()->pluck('name', 'id')->toArray();
    }
}
