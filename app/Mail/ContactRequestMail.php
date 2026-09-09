<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class ContactRequestMail extends Mailable
{
    public array $contact;

    public function __construct(array $contact)
    {
        $this->contact = $contact;
    }

    public function build()
    {
        return $this->subject('EBC — Yêu cầu liên hệ')
            ->replyTo($this->contact['email'])
            ->view('emails.contact-request');
    }
}
