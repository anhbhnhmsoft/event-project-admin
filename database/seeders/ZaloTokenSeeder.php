<?php

namespace Database\Seeders;

use App\Services\ZaloService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class ZaloTokenSeeder extends Seeder
{
    private ZaloService $zaloService;

    public function __construct(ZaloService $zaloService)
    {
        $this->zaloService = $zaloService;
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $accessToken = env('ZALO_INIT_ACCESS_TOKEN');
        $refreshToken = env('ZALO_INIT_REFRESH_TOKEN');

        if (!$accessToken || !$refreshToken) {
            $this->command->warn('Skipping Zalo Token Seeder: ZALO_INIT_ACCESS_TOKEN or ZALO_INIT_REFRESH_TOKEN not set in .env');
            return;
        }

        try {
            $this->zaloService->setTokens($accessToken, $refreshToken, 60 * 60 * 5);
            $this->command->info('Zalo tokens seeded successfully!');
        } catch (\Throwable $e) {
            $this->command->error('Failed to seed Zalo tokens: ' . $e->getMessage());
            Log::error('ZaloTokenSeeder failed', ['error' => $e->getMessage()]);
        }
    }
}
