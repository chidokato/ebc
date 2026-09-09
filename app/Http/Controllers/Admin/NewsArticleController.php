<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NewsArticle;
use App\Support\ImageResizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class NewsArticleController extends Controller
{
    public function index(Request $request)
    {
        $locale = $request->query('locale', 'vi');
        abort_unless(array_key_exists($locale, NewsArticle::LANGUAGES), 404);
        return view('admin.news.index', [
            'articles' => NewsArticle::where('locale', $locale)->latest()->paginate(20)->withQueryString(),
            'locale' => $locale, 'locales' => NewsArticle::LANGUAGES,
        ]);
    }

    public function create(Request $request)
    {
        $locale = $request->query('locale', 'vi');
        abort_unless(array_key_exists($locale, NewsArticle::LANGUAGES), 404);
        $group = $request->query('group');
        if ($group) {
            NewsArticle::where('translation_group', $group)->firstOrFail();
            abort_if(NewsArticle::where('translation_group', $group)->where('locale', $locale)->exists(), 409);
        }
        return $this->form(new NewsArticle(['locale' => $locale, 'translation_group' => $group]));
    }

    public function store(Request $request)
    {
        return $this->save($request, new NewsArticle());
    }

    public function edit(NewsArticle $news)
    {
        return $this->form($news);
    }

    public function update(Request $request, NewsArticle $news)
    {
        return $this->save($request, $news);
    }

    private function form(NewsArticle $article)
    {
        return view('admin.news.form', [
            'article' => $article, 'locales' => NewsArticle::LANGUAGES,
            'translations' => $article->translation_group ? NewsArticle::where('translation_group', $article->translation_group)->get()->keyBy('locale') : collect(),
        ]);
    }

    private function save(Request $request, NewsArticle $article)
    {
        $data = $request->validate([
            'locale' => ['required', Rule::in(array_keys(NewsArticle::LANGUAGES))],
            'translation_group' => ['nullable', 'uuid', 'exists:news_articles,translation_group'],
            'title' => ['required', 'string', 'max:255'],
            'excerpt' => ['nullable', 'string', 'max:2000'],
            'content' => ['required', 'string', 'max:20000'],
            'content_is_html' => ['nullable', 'boolean'],
            'image_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'apply_image_all_languages' => ['nullable', 'boolean'],
            'status' => ['required', Rule::in(['draft', 'published'])],
        ]);
        if ($article->exists) {
            $data['locale'] = $article->locale;
        }
        $group = $article->translation_group ?: ($data['translation_group'] ?? (string) Str::uuid());
        if (! $article->exists && NewsArticle::where('translation_group', $group)->where('locale', $data['locale'])->exists()) {
            throw \Illuminate\Validation\ValidationException::withMessages(['locale' => 'Bản ngôn ngữ này đã tồn tại. Vui lòng mở bài để sửa.']);
        }
        $data['excerpt'] = $data['excerpt'] ?? '';
        $data['content_is_html'] = $request->boolean('content_is_html');
        if ($data['content_is_html']) {
            $data['content'] = \App\Support\NewsHtml::clean($data['content']);
            $text = html_entity_decode(strip_tags($data['content']), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            if (preg_replace('/[\s\x{00a0}\x{200b}]+/u', '', $text) === '') {
                throw \Illuminate\Validation\ValidationException::withMessages(['content' => 'Vui lòng nhập nội dung bài viết.']);
            }
        }
        $applyImageAllLanguages = $request->boolean('apply_image_all_languages');
        if ($applyImageAllLanguages && ! $request->hasFile('image_file') && ! $article->image_path) {
            throw \Illuminate\Validation\ValidationException::withMessages(['image_file' => 'Vui lòng chọn ảnh đại diện trước khi áp dụng cho tất cả ngôn ngữ.']);
        }
        $imagePath = $request->hasFile('image_file') ? ImageResizer::store($request->file('image_file'), 'uploads/news') : $article->image_path;
        DB::transaction(function () use ($article, $data, $group, $imagePath, $applyImageAllLanguages) {
            $article->fill(collect($data)->only(['locale', 'title', 'excerpt', 'content', 'content_is_html'])->all());
            $article->translation_group = $group;
            $article->image_path = $imagePath;
            $article->published_at = $data['status'] === 'published' ? ($article->published_at ?: now()) : null;
            $article->save();
            if ($applyImageAllLanguages) {
                NewsArticle::where('translation_group', $group)
                    ->where('id', '!=', $article->id)
                    ->update(['image_path' => $imagePath]);
            }
        });
        return redirect()->route('admin.news.edit', $article)->with('success', $applyImageAllLanguages
            ? 'Đã lưu bài viết và áp dụng ảnh đại diện cho tất cả bản ngôn ngữ hiện có của bài viết.'
            : 'Đã lưu bài viết.');
    }

    public function destroy(NewsArticle $news)
    {
        $locale = $news->locale;
        $news->delete();
        return redirect()->route('admin.news.index', ['locale' => $locale])->with('success', 'Đã xóa bản tin của ngôn ngữ này.');
    }
}
