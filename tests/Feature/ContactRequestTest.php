<?php

namespace Tests\Feature;

use App\Mail\ContactRequestMail;
use App\Models\WebsiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ContactRequestTest extends TestCase
{
    use RefreshDatabase;

    private function payload(): array
    {
        return ['service' => 'Tổ chức sự kiện', 'name' => 'Nguyễn An', 'email' => 'customer@example.com',
            'phone' => '0986003663', 'message' => "Tư vấn sự kiện\n100 khách"];
    }

    public function test_contact_uses_admin_recipient_and_customer_reply_address(): void
    {
        Mail::fake();
        config(['mail.default' => 'smtp', 'mail.booking_to' => 'fallback@example.com']);
        WebsiteSetting::create(['title' => 'EBC', 'booking_email' => 'admin@example.com']);
        $this->postJson('/contact-request', $this->payload())->assertOk();
        Mail::assertSent(ContactRequestMail::class, function ($mail) {
            $mail->build();
            return $mail->hasTo('admin@example.com') && $mail->hasReplyTo('customer@example.com')
                && $mail->contact === $this->payload();
        });
        $html = view('emails.contact-request', ['contact' => array_replace($this->payload(), ['message' => '<script>alert(1)</script>'])])->render();
        $this->assertStringContainsString('&lt;script&gt;', $html);
        $this->assertStringNotContainsString('<script>', $html);
    }

    public function test_contact_falls_back_to_server_recipient(): void
    {
        Mail::fake();
        config(['mail.default' => 'smtp', 'mail.booking_to' => 'fallback@example.com']);
        $this->postJson('/contact-request', $this->payload())->assertOk();
        Mail::assertSent(ContactRequestMail::class, fn ($mail) => $mail->hasTo('fallback@example.com'));
    }

    public function test_invalid_contact_is_rejected_without_mail(): void
    {
        Mail::fake();
        $this->postJson('/contact-request', ['service' => 'unknown', 'name' => '', 'email' => 'invalid', 'phone' => 'abc', 'message' => ''])
            ->assertUnprocessable()->assertJsonValidationErrors(['service', 'name', 'email', 'phone', 'message']);
        Mail::assertNothingSent();
    }

    public function test_missing_recipient_and_log_transport_do_not_report_success(): void
    {
        Mail::fake();
        config(['mail.default' => 'smtp', 'mail.booking_to' => null]);
        $this->postJson('/contact-request', $this->payload())->assertStatus(503);
        config(['mail.default' => 'log', 'mail.booking_to' => 'fallback@example.com']);
        $this->postJson('/contact-request', $this->payload())->assertStatus(503);
        Mail::assertNothingSent();
    }

    public function test_mail_failure_is_reported(): void
    {
        config(['mail.default' => 'smtp', 'mail.booking_to' => 'fallback@example.com']);
        Mail::shouldReceive('to')->once()->with('fallback@example.com')->andReturnSelf();
        Mail::shouldReceive('send')->once()->andThrow(new \RuntimeException('Transport unavailable'));
        $this->postJson('/contact-request', $this->payload())->assertStatus(503);
    }
}
