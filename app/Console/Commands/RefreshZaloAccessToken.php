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
    public function handle()
    {
        Log::info('Console Command: zalo:refresh-token started.');

        // Refresh token (Service handles fetching from DB if not passed
        $result = $this->zaloService->refreshAccessToken();

        if (!$result['success']) {
            Log::error('RefreshZaloAccessToken command failed', [
                'error' => $result['message'],
            ]);
            return;
        }

        Log::info('Access token refreshed successfully!');
        Log::info('Expires in: ' . ($result['expires_in'] ?? 'unknown') . ' seconds');

        if (isset($result['refresh_token'])) {
            Log::info('New refresh token also cached');
        }

        Log::info('RefreshZaloAccessToken command completed successfully');
    }
}
