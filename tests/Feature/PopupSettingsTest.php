<?php

namespace Tests\Feature;

use App\Models\PopupSetting;
use App\Models\Slider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class PopupSettingsTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $extra = []): array
    {
        return array_replace([
            'is_enabled' => 1, 'title' => 'Ưu đãi mới', 'content' => "Nội dung mới\nDòng thứ hai",
            'offer_content' => 'Giảm 30%', 'launch_label' => 'Xem ưu đãi', 'submit_label' => 'Gửi đăng ký',
        ], $extra);
    }

    public function test_admin_can_save_content_and_toggle_popup_for_all_languages(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $this->get(route('admin.popup-settings.edit'))->assertOk()->assertSee('Cấu hình popup');
        $this->get(route('home', ['locale' => 'vi']))->assertOk()->assertSee('id="booking-popup"', false);
        Slider::create(['locale' => 'vi', 'title' => 'Slider', 'image_path' => 'frontend/img/popup.jpg', 'button_label' => 'Đăng ký', 'is_active' => true, 'sort_order' => 0]);
        $this->put(route('admin.popup-settings.update'), $this->payload())->assertSessionHasNoErrors()->assertRedirect(route('admin.popup-settings.edit'));
        foreach (['vi', 'en', 'zh', 'ko'] as $locale) {
            $this->get(route('home', ['locale' => $locale]))->assertOk()
                ->assertSee('Ưu đãi mới')->assertSee('Nội dung mới')->assertSee('Giảm 30%')->assertSee('Xem ưu đãi')->assertSee('Gửi đăng ký');
        }
        $this->put(route('admin.popup-settings.update'), $this->payload(['is_enabled' => 0]))->assertSessionHasNoErrors();
        $this->assertFalse(PopupSetting::current()->is_enabled);
        $this->get(route('home', ['locale' => 'vi']))->assertOk()->assertDontSee('id="booking-popup"', false)
            ->assertDontSee('data-open-booking', false)->assertDontSee('class="booking-popup-launch"', false)->assertSee('data-consultation', false);
        $this->put(route('admin.popup-settings.update'), $this->payload())->assertSessionHasNoErrors();
        $this->get(route('home', ['locale' => 'vi']))->assertOk()->assertSee('id="booking-popup"', false)->assertSee('data-open-booking', false);
        $this->assertDatabaseCount('popup_settings', 1);
    }

    public function test_images_are_saved_preserved_replaced_and_reset(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $paths = [];
        try {
            foreach (['first', 'replacement'] as $name) {
                $this->put(route('admin.popup-settings.update'), $this->payload([
                    'image_file' => UploadedFile::fake()->image($name.'.jpg'),
                    'offer_image_file' => UploadedFile::fake()->image($name.'.png'),
                ]))->assertSessionHasNoErrors();
                $settings = PopupSetting::current();
                foreach ([$settings->image_path, $settings->offer_image_path] as $path) {
                    $this->assertNotNull($path);
                    $this->assertNotContains($path, $paths);
                    $paths[] = $path;
                    $this->assertFileExists(base_path($path));
                    $this->get(route('home', ['locale' => 'vi']))->assertOk()->assertSee(asset($path), false);
                    $this->get(route('admin.popup-settings.edit'))->assertOk()->assertSee(asset($path), false);
                }
                $this->put(route('admin.popup-settings.update'), $this->payload())->assertSessionHasNoErrors();
                $this->assertSame($settings->image_path, PopupSetting::current()->image_path);
                $this->assertSame($settings->offer_image_path, PopupSetting::current()->offer_image_path);
            }
            $this->put(route('admin.popup-settings.update'), $this->payload(['remove_image' => 1, 'remove_offer_image' => 1]))->assertSessionHasNoErrors();
            $this->assertNull(PopupSetting::current()->image_path);
            $this->assertNull(PopupSetting::current()->offer_image_path);
            $this->get(route('home', ['locale' => 'vi']))->assertOk()->assertSee(asset('frontend/img/popup.jpg'), false)->assertSee(asset('frontend/img/50.png'), false);
        } finally {
            foreach ($paths as $path) File::delete(base_path($path));
        }
    }

    public function test_input_is_validated_and_content_is_escaped(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $this->put(route('admin.popup-settings.update'), $this->payload([
            'title' => '', 'is_enabled' => 'invalid', 'image_file' => UploadedFile::fake()->create('bad.txt'),
            'offer_image_file' => UploadedFile::fake()->image('large.png')->size(5121),
        ]))->assertSessionHasErrors(['title', 'is_enabled', 'image_file', 'offer_image_file']);
        $this->assertDatabaseCount('popup_settings', 0);
        $script = '<script>alert("popup")</script>';
        $this->put(route('admin.popup-settings.update'), $this->payload(['title' => $script, 'content' => $script, 'offer_content' => $script]))->assertSessionHasNoErrors();
        $this->get(route('home', ['locale' => 'vi']))->assertOk()->assertDontSee($script, false)->assertSee(e($script), false);
    }

    public function test_only_admins_can_change_popup(): void
    {
        $this->get(route('admin.popup-settings.edit'))->assertRedirect();
        $this->put(route('admin.popup-settings.update'), $this->payload())->assertRedirect();
        $this->actingAs(User::factory()->create(['is_admin' => false]));
        $this->get(route('admin.popup-settings.edit'))->assertRedirect();
        $this->put(route('admin.popup-settings.update'), $this->payload())->assertRedirect();
        $this->assertDatabaseCount('popup_settings', 0);
    }
}
