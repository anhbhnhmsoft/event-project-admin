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
        $result = $authService->checkExpiresAtUser();

        if (!($result['status'] ?? false)) {
            $this->error(__($result['message']));
            return Command::FAILURE;
        }

        $deletedCount = $result['deleted_count'] ?? 0;
        $this->info(__($result['message']) . " Deleted reset codes: {$deletedCount}");

        return Command::SUCCESS;
    }
}
