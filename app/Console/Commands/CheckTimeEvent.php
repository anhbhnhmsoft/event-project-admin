<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\EventService;

class CheckTimeEvent extends Command
{
    protected $signature = 'app:check-time-event';
    protected $description = 'Check time event';

    public function handle(EventService $eventService)
    {
        $result = $eventService->checkTimeEvent();
        $this->info($result['message']);
        return Command::SUCCESS;
    }
}