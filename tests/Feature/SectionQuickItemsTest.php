<?php

namespace Tests\Feature;

use App\Models\HomepageSection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class SectionQuickItemsTest extends TestCase
{
    use RefreshDatabase;

    public function test_sharing_replaces_and_clears_only_matching_translations(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $original = [['name' => 'Area', 'sub' => '100', 'icon_path' => 'area.svg']];
        foreach (['vi', 'en', 'zh', 'ko'] as $locale) {
            HomepageSection::create(['locale' => $locale, 'translation_group' => 'shared', 'title' => $locale, 'quick_items' => $original]);
        }
        $source = HomepageSection::where('locale', 'en')->firstOrFail();
        $unrelated = HomepageSection::create(['locale' => 'vi', 'translation_group' => 'other', 'title' => 'Other', 'quick_items' => $original]);
        $this->get('/admin/homepage-sections/'.$source->id.'/edit')->assertOk()->assertSee('apply_quick_items_all');
        $payload = ['title' => 'en', 'sort_order' => 0, 'quick_items_present' => 1, 'apply_quick_items_all' => 1,
            'quick_items' => [['name' => 'Capacity', 'sub' => '300'], ['existing_index' => 0, 'name' => 'Area', 'sub' => '430']]];
        $this->put('/admin/homepage-sections/'.$source->id, $payload)->assertSessionHasNoErrors();
        foreach (HomepageSection::where('translation_group', 'shared')->get() as $translation) {
            $this->assertSame($source->fresh()->quick_items, $translation->quick_items);
            $this->assertSame('area.svg', $translation->quick_items[1]['icon_path']);
            $this->assertSame($translation->locale, $translation->title);
        }
        $payload['apply_quick_items_all'] = 0;
        unset($payload['quick_items']);
        $this->put('/admin/homepage-sections/'.$source->id, $payload)->assertSessionHasNoErrors();
        $this->assertSame([], $source->fresh()->quick_items);
        $this->assertCount(2, HomepageSection::where('translation_group', 'shared')->where('locale', 'vi')->firstOrFail()->quick_items);
        $payload['apply_quick_items_all'] = 1;
        $this->put('/admin/homepage-sections/'.$source->id, $payload)->assertSessionHasNoErrors();
        foreach (HomepageSection::where('translation_group', 'shared')->get() as $translation) {
            $this->assertSame([], $translation->quick_items);
        }
        $this->assertSame($original, $unrelated->fresh()->quick_items);
    }

    public function test_create_with_quick_item_sharing_disabled_only_populates_selected_language(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $this->post('/admin/homepage-sections', ['locale' => 'vi', 'title' => 'Elite', 'sort_order' => 0,
            'quick_items_present' => 1, 'apply_quick_items_all' => 0, 'quick_items' => [['name' => 'Area', 'sub' => '430']]])->assertSessionHasNoErrors();
        $this->assertCount(1, HomepageSection::where('locale', 'vi')->firstOrFail()->quick_items);
        foreach (HomepageSection::where('locale', '!=', 'vi')->get() as $translation) {
            $this->assertSame([], $translation->quick_items);
        }
    }

    public function test_quick_items_save_render_edit_and_clear(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $parent = HomepageSection::create(['locale' => 'vi', 'title' => 'Ballroom', 'key' => 'ballroom', 'is_active' => true]);
        $payload = ['locale' => 'vi', 'parent_id' => $parent->id, 'title' => 'Elite', 'sort_order' => 0, 'is_active' => 1,
            'quick_items_present' => 1, 'quick_items' => [['name' => 'Area', 'sub' => '430m²'], ['name' => 'Capacity', 'sub' => '300']]];
        $this->post('/admin/homepage-sections', $payload)->assertSessionHasNoErrors()->assertRedirect();
        $section = HomepageSection::where('title', 'Elite')->where('locale', 'vi')->firstOrFail();
        $this->assertCount(2, $section->quick_items);
        $this->assertSame('430m²', $section->quick_items[0]['sub']);
        $this->get('/admin/homepage-sections/'.$section->id.'/edit')->assertOk()->assertSee('430m²');
        $this->get('/vi')->assertOk()->assertSee('430m²')->assertSee('Capacity');
        $payload['quick_items'] = [['existing_index' => 1, 'name' => 'Capacity', 'sub' => '500']];
        $this->put('/admin/homepage-sections/'.$section->id, $payload)->assertSessionHasNoErrors();
        $this->assertSame('500', $section->fresh()->quick_items[0]['sub']);
        $this->assertSame('300', HomepageSection::where('title', 'Elite')->where('locale', 'en')->firstOrFail()->quick_items[1]['sub']);
        unset($payload['quick_items']);
        $this->put('/admin/homepage-sections/'.$section->id, $payload)->assertSessionHasNoErrors();
        $this->assertSame([], $section->fresh()->quick_items);
    }

    public function test_invalid_rows_are_rejected_and_icon_paths_cannot_be_injected(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $payload = ['locale' => 'vi', 'title' => 'Elite', 'sort_order' => 0, 'quick_items_present' => 1,
            'quick_items' => [['name' => '', 'sub' => '300']]];
        $this->post('/admin/homepage-sections', $payload)->assertSessionHasErrors('quick_items.0.name');
        $payload['quick_items'] = [['name' => 'Area', 'icon_path' => 'https://example.com/icon.svg']];
        $this->post('/admin/homepage-sections', $payload)->assertSessionHasErrors('quick_items.0');
    }

    public function test_uploaded_icon_is_preserved_on_edit_and_can_be_removed(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $section = HomepageSection::create(['locale' => 'vi', 'title' => 'Elite']);
        $payload = ['locale' => 'vi', 'title' => 'Elite', 'sort_order' => 0, 'quick_items_present' => 1,
            'quick_items' => [['name' => 'Area', 'sub' => '430m²', 'icon_file' => UploadedFile::fake()->createWithContent('area.svg', '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32"><rect width="30" height="30"/></svg>')]]];
        $path = null;
        try {
            $this->put('/admin/homepage-sections/'.$section->id, $payload)->assertSessionHasNoErrors();
            $path = $section->fresh()->quick_items[0]['icon_path'];
            $this->assertFileExists(base_path($path));
            $payload['quick_items'] = [['existing_index' => 0, 'name' => 'Area', 'sub' => '500m²']];
            $this->put('/admin/homepage-sections/'.$section->id, $payload)->assertSessionHasNoErrors();
            $this->assertSame($path, $section->fresh()->quick_items[0]['icon_path']);
            $payload['quick_items'][0]['remove_icon'] = 1;
            $this->put('/admin/homepage-sections/'.$section->id, $payload)->assertSessionHasNoErrors();
            $this->assertNull($section->fresh()->quick_items[0]['icon_path']);
        } finally {
            if ($path) File::delete(base_path($path));
        }
    }
}
