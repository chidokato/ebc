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

    public function test_settings_render_publicly_and_code_is_escaped_in_admin(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $this->get('/admin/settings')->assertOk();
        $payload = ['title' => 'My Website', 'seo_title' => 'Event Venue', 'seo_description' => 'Venue description', 'seo_keywords' => 'event, meeting',
            'head_code' => '<script data-test="head">window.siteTest=1;</script>', 'footer_code' => '<div id="custom-footer">Footer widget</div>'];
        $this->put('/admin/settings', $payload)->assertSessionHasNoErrors()->assertRedirect();
        $this->assertDatabaseCount('website_settings', 1);
        $this->get('/vi')->assertOk()->assertSee('<title>Event Venue</title>', false)->assertSee($payload['head_code'], false)->assertSee($payload['footer_code'], false)->assertSee('Venue description');
        $this->get('/en/news')->assertOk()->assertSee('My Website')->assertSee($payload['head_code'], false);
        $this->get('/admin/settings')->assertOk()->assertDontSee($payload['head_code'], false)->assertSee(e($payload['head_code']), false);
        $payload['head_code'] = '';
        $payload['footer_code'] = '';
        $this->put('/admin/settings', $payload)->assertSessionHasNoErrors();
        $this->assertDatabaseCount('website_settings', 1);
        $this->get('/vi')->assertDontSee('window.siteTest=1;', false);
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
                $this->get('/vi')->assertSee(asset($path), false);
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
