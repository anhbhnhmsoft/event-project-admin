<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AuthService;

class CheckExpiresAtUser extends Command
{
    protected $signature = 'app:check-expires-at-user';
    protected $description = 'Check expires at user';

    public function handle(AuthService $authService)
    {
        $count = $authService->checkExpiresAtUser();
        $this->info("Check expires {$count} users success");
        return Command::SUCCESS;
    }
}