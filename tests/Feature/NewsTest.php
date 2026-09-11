<?php

namespace Tests\Feature;

use App\Models\NewsArticle;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class NewsTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $extra = []): array
    {
        return array_replace(['locale' => 'vi', 'title' => 'Tin mới', 'excerpt' => 'Tóm tắt', 'content' => 'Nội dung bài viết', 'status' => 'published'], $extra);
    }

    private function admin(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        Http::fake();
    }

    public function test_manual_languages_render_and_are_edited_independently(): void
    {
        $this->admin();
        $this->post('/admin/news', $this->payload())->assertSessionHasNoErrors()->assertRedirect();
        $source = NewsArticle::firstOrFail();
        foreach (['en', 'zh', 'ko'] as $locale) {
            $this->get('/admin/news/create?locale='.$locale.'&group='.$source->translation_group)->assertOk();
            $this->post('/admin/news', $this->payload(['locale' => $locale, 'translation_group' => $source->translation_group, 'title' => 'Manual '.$locale]))->assertSessionHasNoErrors();
        }
        $this->assertDatabaseCount('news_articles', 4);
        foreach (NewsArticle::all() as $article) {
            $this->get(\App\Support\LocalizedUrl::route('news.show', ['locale' => $article->locale, 'news' => $article->id]))->assertOk()->assertSee($article->title);
            $this->get('/admin/news/'.$article->id.'/edit')->assertOk()->assertDontSee('name="translate"', false);
            $this->get(\App\Support\LocalizedUrl::route('home', ['locale' => $article->locale]))->assertOk()->assertSee($article->title);
        }
        $this->get('/admin/news')->assertOk();
        $this->put('/admin/news/'.$source->id, $this->payload(['title' => 'Đã sửa', 'status' => 'draft']))->assertSessionHasNoErrors();
        $english = NewsArticle::where('locale', 'en')->firstOrFail();
        $this->assertSame('Manual en', $english->title);
        $this->assertNotNull($english->published_at);
        $this->delete('/admin/news/'.$source->id)->assertRedirect();
        $this->assertDatabaseCount('news_articles', 3);
        Http::assertNothingSent();
    }

    public function test_legacy_translation_inputs_do_not_trigger_external_requests(): void
    {
        $this->admin();
        $this->post('/admin/news', $this->payload(['translate' => 1, 'overwrite_translations' => 1]))->assertSessionHasNoErrors();
        $this->assertDatabaseCount('news_articles', 1);
        Http::assertNothingSent();
    }

    public function test_shared_image_only_updates_translations_of_the_same_article(): void
    {
        $this->admin();
        $this->post('/admin/news', $this->payload())->assertSessionHasNoErrors();
        $source = NewsArticle::firstOrFail();
        $source->update(['image_path' => 'uploads/news/shared.jpg']);
        foreach (['en', 'zh', 'ko'] as $locale) {
            $this->post('/admin/news', $this->payload([
                'locale' => $locale, 'translation_group' => $source->translation_group,
                'title' => 'Manual '.$locale, 'status' => 'draft',
            ]))->assertSessionHasNoErrors();
        }
        $translations = NewsArticle::where('id', '!=', $source->id)->get();
        foreach ($translations as $translation) {
            $translation->update(['image_path' => 'uploads/news/old.jpg']);
        }
        $this->post('/admin/news', $this->payload())->assertSessionHasNoErrors();
        $unrelated = NewsArticle::latest('id')->firstOrFail();
        $this->get('/admin/news/'.$source->id.'/edit')->assertOk()->assertSee('Áp dụng cho tất cả ngôn ngữ');
        $this->put('/admin/news/'.$source->id, $this->payload())->assertSessionHasNoErrors();
        $this->assertSame('uploads/news/old.jpg', $translations->first()->fresh()->image_path);
        $this->put('/admin/news/'.$source->id, $this->payload(['apply_image_all_languages' => 1]))->assertSessionHasNoErrors();
        foreach ($translations as $translation) {
            $updated = $translation->fresh();
            $this->assertSame('uploads/news/shared.jpg', $updated->image_path);
            $this->assertSame($translation->title, $updated->title);
            $this->assertSame($translation->content, $updated->content);
            $this->assertNull($updated->published_at);
        }
        $this->assertNull($unrelated->fresh()->image_path);
        $this->assertDatabaseCount('news_articles', 5);
    }

    public function test_applying_without_an_image_does_not_save_the_article(): void
    {
        $this->admin();
        $this->post('/admin/news', $this->payload(['apply_image_all_languages' => 1]))
            ->assertSessionHasErrors('image_file');
        $this->assertDatabaseCount('news_articles', 0);
    }

    public function test_drafts_are_hidden(): void
    {
        $this->admin();
        $this->post('/admin/news', $this->payload(['status' => 'draft']))->assertSessionHasNoErrors();
        $article = NewsArticle::firstOrFail();
        $this->get('/news/'.$article->id)->assertNotFound();
        $this->get('/news')->assertDontSee($article->title);
    }

    public function test_list_status_switch_publishes_and_unpublishes_only_the_selected_translation(): void
    {
        $this->admin();
        $this->post('/admin/news', $this->payload())->assertSessionHasNoErrors();
        $source = NewsArticle::firstOrFail();
        $this->post('/admin/news', $this->payload([
            'locale' => 'en', 'translation_group' => $source->translation_group, 'status' => 'draft',
        ]))->assertSessionHasNoErrors();
        $english = NewsArticle::where('locale', 'en')->firstOrFail();
        $url = '/admin/news/'.$english->id.'/status';
        $this->get('/admin/news?locale=en')->assertOk()->assertSee('role="switch"', false);
        $this->patch($url, ['is_published' => 1, 'page' => 2])->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.news.index', ['locale' => 'en', 'page' => 2]));
        $publishedAt = $english->fresh()->published_at;
        $this->assertNotNull($publishedAt);
        $this->get('/en/news/'.$english->id)->assertOk();
        $this->travel(1)->hours();
        $this->patch($url, ['is_published' => 1])->assertSessionHasNoErrors();
        $this->assertTrue($publishedAt->eq($english->fresh()->published_at));
        $this->patch($url, ['is_published' => 0])->assertSessionHasNoErrors();
        $this->assertNull($english->fresh()->published_at);
        $this->get('/en/news/'.$english->id)->assertNotFound();
        $this->assertNotNull($source->fresh()->published_at);
        $this->assertSame($english->content, $english->fresh()->content);
        $this->patch($url, ['is_published' => 'invalid'])->assertSessionHasErrors('is_published');
        $this->actingAs(User::factory()->create(['is_admin' => false]));
        $this->patch($url, ['is_published' => 1])->assertRedirect(route('admin.login'));
        $this->assertNull($english->fresh()->published_at);
    }

    public function test_rich_content_is_saved_safely_and_can_be_reopened(): void
    {
        $this->admin();
        $this->post('/admin/news', $this->payload([
            'content_is_html' => 1,
            'content' => '<h2>Tiêu đề</h2><p><strong>Đậm</strong> <em>Nghiêng</em></p><ul><li>Mục một</li></ul><a href="https://example.com">Link</a><script>alert(1)</script><img src=x onerror=alert(1)><a href="javascript:alert(1)" onclick="alert(1)">Bad</a>',
        ]))->assertSessionHasNoErrors();
        $article = NewsArticle::firstOrFail();
        $this->assertTrue($article->content_is_html);
        $this->assertStringContainsString('<strong>Đậm</strong>', $article->content);
        $this->assertStringNotContainsString('script', $article->content);
        $this->assertStringNotContainsString('onerror', $article->content);
        $this->assertStringNotContainsString('onclick', $article->content);
        $this->get('/news/'.$article->id)->assertOk()->assertSee('<h2>Tiêu đề</h2>', false)->assertSee('<li>Mục một</li>', false);
        $this->get('/admin/news/'.$article->id.'/edit')->assertOk()->assertSee('ClassicEditor.create', false)->assertSee(e('<strong>Đậm</strong>'), false);
    }

    public function test_empty_editor_content_is_rejected_and_old_text_keeps_line_breaks(): void
    {
        $this->admin();
        $this->post('/admin/news', $this->payload(['content_is_html' => 1, 'content' => '<p>&nbsp;<br></p>']))->assertSessionHasErrors('content');
        $this->assertDatabaseCount('news_articles', 0);
        $this->post('/admin/news', $this->payload(['content' => "Dòng một\n<strong>Văn bản cũ</strong>"]))->assertSessionHasNoErrors();
        $article = NewsArticle::firstOrFail();
        $this->assertStringContainsString('<br>', $article->renderedContent());
        $this->assertStringContainsString('&lt;strong&gt;', $article->renderedContent());
    }

    public function test_authorization_and_locale_isolation(): void
    {
        $this->get('/admin/news')->assertRedirect();
        $this->actingAs(User::factory()->create(['is_admin' => false]));
        $this->post('/admin/news', $this->payload())->assertRedirect(route('admin.login'));
        $this->assertDatabaseCount('news_articles', 0);
        $this->admin();
        $this->post('/admin/news', $this->payload())->assertSessionHasNoErrors();
        $article = NewsArticle::firstOrFail();
        $this->get('/en/news/'.$article->id)->assertNotFound();
        $this->get('/fr/news')->assertNotFound();
        $this->get('/admin/news?locale=fr')->assertNotFound();
    }
}
