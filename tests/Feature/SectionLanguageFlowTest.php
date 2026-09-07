<?php

namespace Tests\Feature;

use App\Models\HomepageSection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SectionLanguageFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_language_tabs_open_separate_records_and_saving_keeps_the_original_locale(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $vi = HomepageSection::create(['locale' => 'vi', 'title' => 'Tiếng Việt', 'translation_group' => 'example']);
        $en = HomepageSection::create(['locale' => 'en', 'title' => 'English', 'translation_group' => 'example']);
        $this->get('/admin/homepage-sections/'.$vi->id.'/edit')->assertOk()
            ->assertDontSee('for="locale"', false)->assertSee(route('admin.homepage-sections.edit', $en), false);
        $this->put('/admin/homepage-sections/'.$vi->id, ['locale' => 'en', 'title' => 'Đã sửa', 'sort_order' => 0])
            ->assertSessionHasNoErrors()->assertRedirect(route('admin.homepage-sections.edit', $vi));
        $this->assertSame('vi', $vi->fresh()->locale);
        $this->assertSame('English', $en->fresh()->title);
    }

    public function test_missing_translation_is_added_once_and_uses_the_localized_parent(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $parentVi = HomepageSection::create(['locale' => 'vi', 'title' => 'Cha', 'translation_group' => 'parent']);
        $parentEn = HomepageSection::create(['locale' => 'en', 'title' => 'Parent', 'translation_group' => 'parent']);
        HomepageSection::create(['locale' => 'vi', 'title' => 'Con', 'parent_id' => $parentVi->id, 'translation_group' => 'child']);
        $this->get('/admin/homepage-sections/create?locale=en&group=child')->assertOk()
            ->assertViewHas('section', fn ($section) => $section->parent_id === $parentEn->id);
        $payload = ['locale' => 'en', 'translation_group' => 'child', 'parent_id' => $parentEn->id, 'title' => 'Child', 'sort_order' => 0];
        $this->post('/admin/homepage-sections', $payload)->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame(2, HomepageSection::where('translation_group', 'child')->count());
        $translation = HomepageSection::where('translation_group', 'child')->where('locale', 'en')->firstOrFail();
        $this->get('/admin/homepage-sections/create?locale=en&group=child')->assertRedirect(route('admin.homepage-sections.edit', $translation));
        $this->post('/admin/homepage-sections', $payload)->assertStatus(409);
        $this->get('/admin/homepage-sections/create?locale=invalid')->assertNotFound();
    }
}
