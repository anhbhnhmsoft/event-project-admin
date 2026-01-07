<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class QuickRegisterSuccessMail extends Mailable
{
    use Queueable, SerializesModels;

    public array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function build()
    {
        return $this->subject('Đăng ký sự kiện thành công / Event Registration Successful')
            ->view('emails.event.quick_register_success')
            ->with($this->data);
    }
}
