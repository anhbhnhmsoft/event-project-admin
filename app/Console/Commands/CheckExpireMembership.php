<?php

namespace App\Console\Commands;

use App\Services\MemberShipService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckExpireMembership extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-expire-membership';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checks for near-to-expire memberships, sends reminders, and marks expired ones.';

    /**
     * Execute the console command.
     */
    public function handle(MemberShipService $memberShipService)
    {
        $this->info('Starting membership expiry check...');
        Log::info('Console Command: app:check-expire-membership started.');
        $result = $memberShipService->checkMembershipExpire();

        if ($result['status']) {

            $this->info('Membership expiry check and notification process completed successfully.');
            Log::info('Console Command: app:check-expire-membership completed successfully.');
        } else {
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
