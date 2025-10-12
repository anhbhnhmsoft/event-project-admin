<?php

namespace App\Jobs;

use App\Mail\EventStartMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendEventEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected array $emails;
    protected string $subject;
    protected array $data;

    public function __construct(array $emails, string $subject, array $data = [])
    {
        $this->emails = $emails;
        $this->subject = $subject;
        $this->data = $data;
    }

    public function handle(): void
    {
        foreach ($this->emails as $email) {
            Mail::to($email)->send(new EventStartMail($this->subject, $this->data));
        }
    }
}
