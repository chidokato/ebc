<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WebsiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class WebsiteSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_social_image_is_uploaded_preserved_replaced_and_reset(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $defaultUrl = WebsiteSetting::current()->social_image_url;
        $paths = [];
        try {
            $this->get(route('admin.settings.edit'))->assertOk()->assertSee('social_image_file')->assertSee($defaultUrl, false);
            foreach (['share.jpg', 'replacement.png'] as $filename) {
                $this->put(route('admin.settings.update'), ['title' => 'EBC', 'social_image_file' => UploadedFile::fake()->image($filename, 1200, 630)])
                    ->assertSessionHasNoErrors()->assertRedirect(route('admin.settings.edit'));
                $path = WebsiteSetting::current()->social_image_path;
                $this->assertNotNull($path);
                $this->assertNotContains($path, $paths);
                $paths[] = $path;
                $this->assertFileExists(base_path($path));
                $this->get(\App\Support\LocalizedUrl::route('home', ['locale' => 'vi']))->assertOk()
                    ->assertSee('<meta property="og:image" content="'.asset($path).'">', false);
                $this->get(\App\Support\LocalizedUrl::route('news.index', ['locale' => 'en']))->assertOk()
                    ->assertSee('<meta property="og:image" content="'.asset($path).'">', false);
                $this->put(route('admin.settings.update'), ['title' => 'Updated'])->assertSessionHasNoErrors();
                $this->assertSame($path, WebsiteSetting::current()->social_image_path);
            }
            $this->put(route('admin.settings.update'), ['title' => 'EBC', 'remove_social_image' => 1])->assertSessionHasNoErrors();
            $this->assertNull(WebsiteSetting::current()->social_image_path);
            $this->get(\App\Support\LocalizedUrl::route('home', ['locale' => 'vi']))->assertOk()
                ->assertSee('<meta property="og:image" content="'.$defaultUrl.'">', false);
        } finally {
            foreach ($paths as $path) File::delete(base_path($path));
        }
    }

    public function test_social_image_rejects_invalid_or_oversized_files(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        foreach ([UploadedFile::fake()->create('bad.txt'), UploadedFile::fake()->image('large.jpg')->size(5121)] as $file) {
            $this->put(route('admin.settings.update'), ['title' => 'EBC', 'social_image_file' => $file])
                ->assertSessionHasErrors('social_image_file');
        }
        $this->assertDatabaseCount('website_settings', 0);
    }

    public function test_booking_email_can_be_saved_validated_and_cleared(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $this->put('/admin/settings', ['title' => 'Website', 'booking_email' => 'bookings@example.com'])->assertSessionHasNoErrors();
        $this->assertSame('bookings@example.com', WebsiteSetting::current()->booking_email);
        $this->get('/admin/settings')->assertOk()->assertSee('Email nhận đăng ký')->assertSee('bookings@example.com');
        $this->put('/admin/settings', ['title' => 'Website', 'booking_email' => 'invalid'])->assertSessionHasErrors('booking_email');
        $this->assertSame('bookings@example.com', WebsiteSetting::current()->booking_email);
        $this->put('/admin/settings', ['title' => 'Website', 'booking_email' => ''])->assertSessionHasNoErrors();
        $this->assertNull(WebsiteSetting::current()->booking_email);
    }

    public function test_settings_render_publicly_and_code_is_escaped_in_admin(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $this->get('/admin/settings')->assertOk();
        $payload = ['title' => 'My Website', 'seo_title' => 'Event Venue', 'seo_description' => 'Venue description', 'seo_keywords' => 'event, meeting',
            'head_code' => '<script data-test="head">window.siteTest=1;</script>', 'footer_code' => '<div id="custom-footer">Footer widget</div>'];
        $this->put('/admin/settings', $payload)->assertSessionHasNoErrors()->assertRedirect();
        $this->assertDatabaseCount('website_settings', 1);
        $this->get('/')->assertOk()->assertSee('<title>Event Venue</title>', false)->assertSee($payload['head_code'], false)->assertSee($payload['footer_code'], false)->assertSee('Venue description');
        $this->get('/en/news')->assertOk()->assertSee('My Website')->assertSee($payload['head_code'], false);
        $this->get('/admin/settings')->assertOk()->assertDontSee($payload['head_code'], false)->assertSee(e($payload['head_code']), false);
        $payload['head_code'] = '';
        $payload['footer_code'] = '';
        $this->put('/admin/settings', $payload)->assertSessionHasNoErrors();
        $this->assertDatabaseCount('website_settings', 1);
        $this->get('/')->assertDontSee('window.siteTest=1;', false);
    }

    public function test_uploads_are_saved_preserved_and_can_revert_to_defaults(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $paths = [];
        try {
            $this->put('/admin/settings', ['title' => 'Brand', 'logo_file' => UploadedFile::fake()->image('logo.png'), 'white_logo_file' => UploadedFile::fake()->image('white-logo.png'), 'favicon_file' => UploadedFile::fake()->image('favicon.png', 32, 32)])->assertSessionHasNoErrors();
            $settings = WebsiteSetting::current();
            $paths = [$settings->logo_path, $settings->favicon_path, $settings->white_logo_path];
            foreach ($paths as $path) {
                $this->assertFileExists(base_path($path));
                $this->get('/')->assertSee(asset($path), false);
            }
            $this->put('/admin/settings', ['title' => 'Brand updated'])->assertSessionHasNoErrors();
            $this->assertSame($paths[0], WebsiteSetting::current()->logo_path);
            $this->put('/admin/settings', ['title' => 'Brand', 'remove_logo' => 1, 'remove_favicon' => 1, 'remove_white_logo' => 1])->assertSessionHasNoErrors();
            $this->assertNull(WebsiteSetting::current()->white_logo_path);
            $this->assertNull(WebsiteSetting::current()->logo_path);
            $this->assertNull(WebsiteSetting::current()->favicon_path);
        } finally {
            foreach ($paths as $path) File::delete(base_path($path));
        }
    }

    public function test_settings_require_admin_and_validate_input(): void
    {
        $this->get('/admin/settings')->assertRedirect();
        $this->actingAs(User::factory()->create(['is_admin' => false]));
        $this->put('/admin/settings', ['title' => 'Unauthorized'])->assertRedirect();
        $this->assertDatabaseCount('website_settings', 0);
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $this->put('/admin/settings', ['title' => '', 'favicon_file' => UploadedFile::fake()->create('bad.txt')])->assertSessionHasErrors(['title', 'favicon_file']);
        $this->assertDatabaseCount('website_settings', 0);
    }
}
