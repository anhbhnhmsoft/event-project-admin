<?php

namespace App\Jobs;

use App\Mail\MembershipExpireMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendMembershipExpireEmail implements ShouldQueue
{
    use Queueable, Dispatchable, InteractsWithQueue, SerializesModels;

    protected array $userEmails;
    protected string $title;
    protected string $message;

    public function __construct(array $userEmails, string $title, string $message)
    {
        $this->userEmails = $userEmails;
        $this->title = $title;
        $this->message = $message;
    }

    public function handle(): void
    {
        foreach ($this->userEmails as $email) {
            try {
                Mail::to($email)->send(new MembershipExpireMail($this->title, $this->message));
            } catch (\Throwable $e) {
                Log::error('SendEmail: Lỗi gửi email', [
                    'email' => $email,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}
