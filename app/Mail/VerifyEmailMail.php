<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;

class VerifyEmailMail extends Mailable
{
    use Queueable, SerializesModels;

    public $url;
    public $locale;
    public $user;
    /**
     * Create a new message instance.
     */
    public function __construct(string $url, string $locale = 'vi', $user)
    {
        $this->url = $url;
        $this->locale = $locale;
        $this->user = $user;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        App::setLocale($this->locale);
        
        $subject = ($this->locale === 'vi') 
            ? 'Xác thực Email' 
            : 'Verify Email';
        
        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.auth.verify-email',
            with: [
                'url' => $this->url,
                'user' => $this->user,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
