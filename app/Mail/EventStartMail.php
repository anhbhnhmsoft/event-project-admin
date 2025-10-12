<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EventStartMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $subjectText;
    public array $data;

    public function __construct(string $subjectText, array $data)
    {
        $this->subjectText = $subjectText;
        $this->data = $data;
    }

    public function build()
    {
        return $this->subject($this->subjectText)
            ->view('emails.event.start')
            ->with($this->data);
    }
}
