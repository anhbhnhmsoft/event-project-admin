<?php

namespace App\Console\Commands;

use App\Services\ZaloService;
use Illuminate\Console\Command;

class SetZaloTokens extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'zalo:set-tokens 
                            {access_token : The access token from Zalo}
                            {refresh_token : The refresh token from Zalo}
                            {--expires=3600 : Token expiration time in seconds}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set initial Zalo access token and refresh token';

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
        $accessToken = $this->argument('access_token');
        $refreshToken = $this->argument('refresh_token');
        $expiresIn = (int) $this->option('expires');

        $this->info('Setting Zalo tokens...');

        try {
            $this->zaloService->setTokens($accessToken, $refreshToken, $expiresIn);

            $this->info('Tokens set successfully!');
            $this->info('Access token will expire in: ' . $expiresIn . ' seconds');
            $this->info('Refresh token cached for 90 days');
            $this->info('');
            $this->info('You can now use: php artisan zalo:refresh-token');

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Failed to set tokens');
            $this->error('Error: ' . $e->getMessage());

            return Command::FAILURE;
        }
    }
}
