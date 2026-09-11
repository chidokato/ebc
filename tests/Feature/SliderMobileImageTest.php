<?php

namespace Tests\Feature;

use App\Models\Slider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class SliderMobileImageTest extends TestCase
{
    use RefreshDatabase;

    public function test_mobile_image_can_be_created_preserved_replaced_and_removed(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $payload = ['locale' => 'vi', 'title' => 'Hero', 'is_active' => 1];
        $paths = [];
        try {
            $this->post('/admin/sliders', $payload + ['image' => UploadedFile::fake()->image('pc.jpg', 100, 50), 'mobile_image' => UploadedFile::fake()->image('mobile.jpg', 50, 100)])->assertRedirect()->assertSessionHasNoErrors();
            $slider = Slider::firstOrFail();
            $paths[] = $pc = $slider->image_path;
            $paths[] = $mobile = $slider->mobile_image_path;
            $this->assertFileExists(base_path($mobile));
            $this->get('/')->assertOk()->assertSee('media="(max-width: 767px)"', false)->assertSee($mobile)->assertSee($pc);
            $this->get('/admin/sliders/'.$slider->id.'/edit')->assertOk()->assertSee('Ảnh mobile');
            $this->get('/admin/sliders')->assertOk()->assertSee($mobile);
            $this->put('/admin/sliders/'.$slider->id, $payload)->assertSessionHasNoErrors();
            $this->assertSame($mobile, $slider->fresh()->mobile_image_path);
            $this->put('/admin/sliders/'.$slider->id, $payload + ['mobile_image' => UploadedFile::fake()->image('new.jpg')])->assertSessionHasNoErrors();
            $paths[] = $replacement = $slider->fresh()->mobile_image_path;
            $this->assertNotSame($mobile, $replacement);
            $this->assertFileDoesNotExist(base_path($mobile));
            $this->assertSame($pc, $slider->fresh()->image_path);
            $this->put('/admin/sliders/'.$slider->id, $payload + ['remove_mobile_image' => 1])->assertSessionHasNoErrors();
            $this->assertNull($slider->fresh()->mobile_image_path);
            $this->assertFileDoesNotExist(base_path($replacement));
            $this->get('/')->assertOk()->assertDontSee('<source media="(max-width: 767px)"', false)->assertSee($pc);
            $this->delete('/admin/sliders/'.$slider->id)->assertRedirect();
            $this->assertFileDoesNotExist(base_path($pc));
        } finally {
            foreach (array_filter($paths) as $path) File::delete(base_path($path));
        }
    }

    public function test_invalid_mobile_upload_is_rejected(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $this->post('/admin/sliders', ['locale' => 'vi', 'mobile_image' => UploadedFile::fake()->create('bad.pdf', 10)])
            ->assertSessionHasErrors('mobile_image');
        $this->assertSame(0, Slider::count());
    }
}
