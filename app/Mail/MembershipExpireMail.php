<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MembershipExpireMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $title;
    public string $message;

    public function __construct(string $title, string $message)
    {
        $this->title = $title;
        $this->message = $message;
    }

    public function build()
    {
        return $this->subject($this->title)
            ->markdown('emails.membership.expire')
            ->with([
                'title' => $this->title,
                'message' => $this->message,
            ]);
    }
}
