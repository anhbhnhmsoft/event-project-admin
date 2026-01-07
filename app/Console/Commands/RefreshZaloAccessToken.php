<?php

namespace App\Console\Commands;

use App\Services\ZaloService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class RefreshZaloAccessToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'zalo:refresh-token';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh Zalo access token using refresh token';

    private ZaloService $zaloService;

    /**
     * Create a new command instance.
     */
    public function __construct(ZaloService $zaloService)
    {
        parent::__construct();
        $this->zaloService = $zaloService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting Zalo access token refresh...');

        // Check if access token exists and is still valid
        if (!$this->option('force')) {
            $currentAccessToken = Cache::get('zalo_access_token');
            if ($currentAccessToken) {
                $this->info('Access token is still valid in cache');
                $this->info('Use --force option to refresh anyway');
                return Command::SUCCESS;
            }
        }

        $this->info('Attempting to refresh token...');

        // Refresh token (Service handles fetching from DB if not passed
        $result = $this->zaloService->refreshAccessToken();

        if (!$result['success']) {
            $this->error('Failed to refresh access token');
            $this->error('Error: ' . $result['message']);

            Log::error('RefreshZaloAccessToken command failed', [
                'error' => $result['message'],
            ]);

            return Command::FAILURE;
        }

        $this->info('Access token refreshed successfully!');
        $this->info('Expires in: ' . ($result['expires_in'] ?? 'unknown') . ' seconds');

        if (isset($result['refresh_token'])) {
            $this->info('New refresh token also cached');
        }

        Log::info('RefreshZaloAccessToken command completed successfully');

        return Command::SUCCESS;
    }
}
