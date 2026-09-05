<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomepageSection;
use App\Models\HomepageSectionImage;
use App\Models\Menu;
use App\Support\ImageResizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class HomepageSectionController extends Controller
{
    public function index(Request $request)
    {
        $locale = $request->query('locale', 'vi');
        abort_unless(in_array($locale, Menu::LOCALES, true), 404);

        return view('admin.homepage-sections.index', [
            'sections' => HomepageSection::where('locale', $locale)->whereNull('parent_id')->with('children')->orderBy('sort_order')->get(),
            'locale' => $locale,
            'locales' => $this->locales(),
        ]);
    }

    public function create(Request $request)
    {
        $locale = $request->query('locale', 'vi');
        $parent = $request->filled('parent') ? HomepageSection::where('locale', $locale)->findOrFail($request->integer('parent')) : null;

        return view('admin.homepage-sections.form', [
            'section' => new HomepageSection(['locale' => $locale, 'parent_id' => $parent?->id, 'is_active' => true]),
            'parents' => HomepageSection::where('locale', $locale)->whereNull('parent_id')->orderBy('sort_order')->get(),
            'locales' => $this->locales(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $imagePaths = $this->storeImages($request);
        $data = $this->storeAssets($request, $data);
        if ($imagePaths) {
            $data['image_path'] = $imagePaths[0];
        }
        $parent = $this->ensureParentMatchesLocale($data['parent_id'] ?? null, $data['locale']);
        $translationGroup = (string) Str::uuid();

        foreach (Menu::LOCALES as $locale) {
            $localized = $data;
            $localized['locale'] = $locale;
            $localized['translation_group'] = $translationGroup;
            $localized['parent_id'] = $parent
                ? HomepageSection::where('translation_group', $parent->translation_group)->where('locale', $locale)->value('id')
                : null;

            $section = HomepageSection::create($localized);
            $this->attachImages($section, $imagePaths);
        }

        return redirect()->route('admin.homepage-sections.index', ['locale' => $data['locale']])->with('success', 'Đã thêm section trang chủ.');
    }

    public function edit(HomepageSection $homepageSection)
    {
        return view('admin.homepage-sections.form', [
            'section' => $homepageSection,
            'parents' => HomepageSection::where('locale', $homepageSection->locale)->whereNull('parent_id')->whereKeyNot($homepageSection->id)->orderBy('sort_order')->get(),
            'locales' => $this->locales(),
        ]);
    }

    public function update(Request $request, HomepageSection $homepageSection)
    {
        $data = $this->validated($request);
        $imagePaths = $this->storeImages($request);
        $data = $this->storeAssets($request, $data, $homepageSection);
        if ($imagePaths && ! $homepageSection->image_path) {
            $data['image_path'] = $imagePaths[0];
        }
        $this->ensureParentMatchesLocale($data['parent_id'] ?? null, $data['locale']);

        if (($data['parent_id'] ?? null) === $homepageSection->id) {
            return back()->withErrors(['parent_id' => 'Một section không thể là section cha của chính nó.']);
        }

        $homepageSection->update($data);
        $this->attachImages($homepageSection, $imagePaths);

        return redirect()->route('admin.homepage-sections.index', ['locale' => $homepageSection->locale])->with('success', 'Đã cập nhật section trang chủ.');
    }

    public function destroy(HomepageSection $homepageSection)
    {
        $locale = $homepageSection->locale;
        $translations = $homepageSection->translation_group
            ? HomepageSection::where('translation_group', $homepageSection->translation_group)->get()
            : collect([$homepageSection]);

        foreach ($translations as $translation) {
            $this->deleteAssetsRecursively($translation);
        }

        foreach ($translations as $translation) {
            $translation->delete();
        }

        return redirect()->route('admin.homepage-sections.index', ['locale' => $locale])->with('success', 'Đã xóa section và các section con ở tất cả ngôn ngữ.');
    }

    public function destroyImage(HomepageSection $homepageSection, HomepageSectionImage $image)
    {
        abort_unless($image->homepage_section_id === $homepageSection->id, 404);

        $path = $image->path;
        $image->delete();

        if ($homepageSection->image_path === $path) {
            $homepageSection->update(['image_path' => $homepageSection->images()->value('path')]);
        }

        $isStillUsed = HomepageSectionImage::where('path', $path)->exists()
            || HomepageSection::where('image_path', $path)->exists();

        if (! $isStillUsed) {
            $this->deleteUploadedAsset($path);
        }

        return back()->with('success', 'Đã xóa ảnh section.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'locale' => ['required', Rule::in(Menu::LOCALES)],
            'parent_id' => ['nullable', 'integer', 'exists:homepage_sections,id'],
            'key' => ['nullable', 'alpha_dash', 'max:80'],
            'title' => ['required', 'string', 'max:255'],
            'sub_title' => ['nullable', 'string', 'max:255'],
            'content' => ['nullable', 'string', 'max:5000'],
            'image_files' => ['nullable', 'array', 'max:20'],
            'image_files.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:20480'],
            'icon_file' => ['nullable', 'file', 'mimes:png,webp,svg', 'max:20480'],
            'link_url' => ['nullable', 'string', 'max:2048'],
            'link_label' => ['nullable', 'string', 'max:100'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
        ]) + ['is_active' => $request->boolean('is_active')];
    }

    private function ensureParentMatchesLocale(?int $parentId, string $locale): ?HomepageSection
    {
        if (! $parentId) {
            return null;
        }

        return HomepageSection::whereKey($parentId)->where('locale', $locale)->firstOrFail();
    }

    private function storeAssets(Request $request, array $data, ?HomepageSection $section = null): array
    {
        unset($data['image_files'], $data['icon_file']);

        foreach (['icon' => 'icon_file'] as $field => $input) {
            if (! $request->hasFile($input)) {
                continue;
            }

            if ($section) {
                $this->deleteUploadedAsset($section->{$field . '_path'});
            }

            $data[$field . '_path'] = ImageResizer::store($request->file($input), 'uploads/homepage-sections/' . $field . 's');
        }

        return $data;
    }

    private function storeImages(Request $request): array
    {
        if (! $request->hasFile('image_files')) {
            return [];
        }

        return collect($request->file('image_files'))
            ->filter()
            ->map(fn ($image) => ImageResizer::store($image, 'uploads/homepage-sections/images'))
            ->all();
    }

    private function attachImages(HomepageSection $section, array $paths): void
    {
        $nextSortOrder = ((int) $section->images()->max('sort_order')) + 1;

        foreach ($paths as $path) {
            $section->images()->create(['path' => $path, 'sort_order' => $nextSortOrder++]);
        }
    }

    private function deleteUploadedAsset(?string $path): void
    {
        if ($path && Str::startsWith($path, 'uploads/homepage-sections/')) {
            File::delete(base_path($path));
        }
    }

    private function deleteAssetsRecursively(HomepageSection $section): void
    {
        foreach ($section->children()->get() as $child) {
            $this->deleteAssetsRecursively($child);
        }

        $this->deleteUploadedAsset($section->image_path);
        $this->deleteUploadedAsset($section->icon_path);

        foreach ($section->images as $image) {
            $this->deleteUploadedAsset($image->path);
        }
    }

    private function locales(): array
    {
        return ['vi' => 'Tiếng Việt', 'en' => 'English', 'zh' => '中文', 'ko' => '한국어'];
    }
}
