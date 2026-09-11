<?php

namespace Tests\Feature;

use App\Support\LocalizedUrl;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DefaultLocaleTest extends TestCase
{
    use RefreshDatabase;

    public function test_root_always_uses_vietnamese_and_other_languages_keep_their_prefix(): void
    {
        $this->withHeader('Accept-Language', 'en-US,en;q=0.9')->get('/')
            ->assertOk()->assertSee('<html lang="vi">', false)->assertSee('Tiếng Việt');
        $this->assertSame(url('/'), LocalizedUrl::route('home', ['locale' => 'vi']));
        $this->assertSame(url('/news'), LocalizedUrl::route('news.index', ['locale' => 'vi']));
        $this->assertSame(url('/news/12'), LocalizedUrl::route('news.show', ['locale' => 'vi', 'news' => 12]));
        $this->get('/news')->assertOk()->assertSee('lang="vi"', false);
        foreach (['en', 'zh', 'ko'] as $locale) {
            $this->get('/'.$locale)->assertOk()->assertSee('<html lang="'.$locale.'">', false)
                ->assertSee('href="'.url('/').'"', false)->assertDontSee('href="'.url('/vi').'"', false);
            $this->get('/'.$locale.'/news')->assertOk()->assertSee('lang="'.$locale.'"', false);
            $this->assertSame(url('/'.$locale), LocalizedUrl::route('home', ['locale' => $locale]));
        }
        $this->get('/fr')->assertNotFound();
    }

    public function test_old_vietnamese_links_redirect_permanently_and_keep_query_parameters(): void
    {
        foreach (['/vi' => '/', '/vi/news' => '/news', '/vi/news/12' => '/news/12'] as $old => $new) {
            $this->get($old)->assertStatus(301)->assertRedirect(url($new));
        }
        $this->get('/vi?utm_source=zalo')->assertStatus(301)->assertRedirect(url('/').'?utm_source=zalo');
        $this->get('/vi/news?page=2')->assertStatus(301)->assertRedirect(url('/news').'?page=2');
        $this->get('/vi/not-a-page')->assertNotFound();
    }
}
