<?php

namespace Tests\Feature;

use App\Models\HomepageSection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class SectionAssetSharingTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $extra = []): array
    {
        return array_replace(['locale' => 'vi', 'title' => 'Elite', 'sort_order' => 0], $extra);
    }

    public function test_switches_copy_only_selected_assets_and_allow_edits_from_other_languages(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $source = HomepageSection::create(['locale' => 'vi', 'translation_group' => 'group-a', 'title' => 'Elite', 'icon_path' => 'source.svg', 'image_path' => 'source.png']);
        $source->images()->create(['path' => 'source.png', 'sort_order' => 1]);
        $target = HomepageSection::create(['locale' => 'en', 'translation_group' => 'group-a', 'title' => 'English title', 'content' => 'English copy', 'icon_path' => 'english.svg', 'image_path' => 'english.png']);
        $target->images()->create(['path' => 'english.png', 'sort_order' => 1]);
        $unrelated = HomepageSection::create(['locale' => 'en', 'translation_group' => 'group-b', 'title' => 'Other', 'icon_path' => 'other.svg']);
        $this->get('/admin/homepage-sections/'.$target->id.'/edit')->assertOk()->assertSee('apply_images_all')->assertSee('apply_icon_all');

        $this->put('/admin/homepage-sections/'.$source->id, $this->payload(['apply_images_all' => 1]))->assertSessionHasNoErrors();
        $this->assertSame(['source.png'], $target->images()->pluck('path')->all());
        $this->assertSame('source.png', $target->fresh()->image_path);
        $this->assertSame('english.svg', $target->fresh()->icon_path);
        $this->assertSame('English title', $target->fresh()->title);
        $this->assertSame('English copy', $target->fresh()->content);

        $this->put('/admin/homepage-sections/'.$target->id, $this->payload(['locale' => 'en', 'apply_icon_all' => 1]))->assertSessionHasNoErrors();
        $this->assertSame('english.svg', $source->fresh()->icon_path);
        $this->assertSame('other.svg', $unrelated->fresh()->icon_path);
        $image = $target->images()->firstOrFail();
        $this->delete('/admin/homepage-sections/'.$target->id.'/images/'.$image->id)->assertRedirect();
        $this->assertSame(['source.png'], $source->images()->pluck('path')->all());
        $this->assertSame([], $target->images()->pluck('path')->all());
    }

    public function test_local_icon_replacement_keeps_the_shared_file_for_other_languages(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $paths = [];
        try {
            $this->post('/admin/homepage-sections', $this->payload(['icon_file' => $this->icon(), 'apply_icon_all' => 1]))->assertSessionHasNoErrors();
            $source = HomepageSection::where('locale', 'vi')->firstOrFail();
            $target = HomepageSection::where('locale', 'en')->firstOrFail();
            $paths[] = $source->icon_path;
            $this->assertSame($source->icon_path, $target->icon_path);
            $this->put('/admin/homepage-sections/'.$target->id, $this->payload(['locale' => 'en', 'icon_file' => $this->icon(), 'apply_icon_all' => 0]))->assertSessionHasNoErrors();
            $paths[] = $target->fresh()->icon_path;
            $this->assertNotSame($paths[0], $paths[1]);
            $this->assertSame($paths[0], $source->fresh()->icon_path);
            $this->assertFileExists(base_path($paths[0]));
        } finally {
            foreach ($paths as $path) File::delete(base_path($path));
        }
    }

    public function test_create_with_sharing_off_keeps_uploads_in_selected_language(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        try {
            $this->post('/admin/homepage-sections', $this->payload(['icon_file' => $this->icon(), 'image_files' => [UploadedFile::fake()->image('room.png')], 'apply_icon_all' => 0, 'apply_images_all' => 0]))->assertSessionHasNoErrors();
            $source = HomepageSection::where('locale', 'vi')->firstOrFail();
            $this->assertNotNull($source->icon_path);
            $this->assertCount(1, $source->images);
            foreach (HomepageSection::where('locale', '!=', 'vi')->get() as $translation) {
                $this->assertNull($translation->icon_path);
                $this->assertNull($translation->image_path);
                $this->assertCount(0, $translation->images);
            }
        } finally {
            foreach (HomepageSection::all() as $section) {
                foreach (array_filter([$section->icon_path, $section->image_path]) as $path) File::delete(base_path($path));
            }
        }
    }

    private function icon(): UploadedFile
    {
        return UploadedFile::fake()->createWithContent('icon.svg', '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32"><rect width="30" height="30"/></svg>');
    }
}
