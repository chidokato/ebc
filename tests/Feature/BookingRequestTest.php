<?php

namespace Tests\Feature;

use App\Mail\BookingRequestMail;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class BookingRequestTest extends TestCase
{
    private function payload(): array
    {
        return ['name' => 'Nguyễn An', 'phone' => '0986003663', 'guests' => 120, 'date' => now()->addDay()->format('Y-m-d')];
    }

    public function test_booking_is_sent_to_configured_recipient(): void
    {
        Mail::fake();
        config(['mail.booking_to' => 'bookings@example.com', 'mail.default' => 'smtp']);
        $this->postJson('/booking-request', $this->payload())->assertOk();
        Mail::assertSent(BookingRequestMail::class, fn ($mail) => $mail->hasTo('bookings@example.com') && $mail->booking === $this->payload());
    }

    public function test_missing_recipient_does_not_report_success(): void
    {
        Mail::fake();
        config(['mail.booking_to' => null]);
        $this->postJson('/booking-request', $this->payload())->assertStatus(503);
        Mail::assertNothingSent();
    }

    public function test_invalid_booking_is_rejected(): void
    {
        Mail::fake();
        $this->postJson('/booking-request', array_replace($this->payload(), ['phone' => 'invalid', 'guests' => 0, 'date' => '2020-01-01']))
            ->assertUnprocessable()->assertJsonValidationErrors(['phone', 'guests', 'date']);
        Mail::assertNothingSent();
    }

    public function test_mail_failure_keeps_request_unsuccessful(): void
    {
        config(['mail.booking_to' => 'bookings@example.com', 'mail.default' => 'smtp']);
        Mail::shouldReceive('to')->once()->with('bookings@example.com')->andReturnSelf();
        Mail::shouldReceive('send')->once()->andThrow(new \RuntimeException('Transport unavailable'));
        $this->postJson('/booking-request', $this->payload())->assertStatus(503);
    }
}
