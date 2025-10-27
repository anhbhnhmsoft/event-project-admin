<?php

namespace App\Console\Commands;

use App\Services\EventService;
use Illuminate\Console\Command;

class NotificationTimeEvent extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:notification-time-event';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(EventService $eventService)
    {
        $result = $eventService->notificationTimeEvent();
        $this->info($result['message']);
        return Command::SUCCESS;
    }
}
