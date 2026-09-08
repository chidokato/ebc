<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class BookingRequestMail extends Mailable
{
    public array $booking;

    public function __construct(array $booking)
    {
        $this->booking = $booking;
    }

    public function build()
    {
        return $this->subject('EBC — Yêu cầu đặt lịch ưu đãi 50%')->view('emails.booking-request');
    }
}
